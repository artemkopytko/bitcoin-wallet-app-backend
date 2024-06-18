<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApiError;
use App\Enums\ApiMessage;
use App\Enums\EventTypes;
use App\Enums\Roles;
use App\Enums\StorageTypes;
use App\Http\Requests\AssetHolding\AssetHoldingReadRequest;
use App\Http\Requests\Auth\ChangeAvatarRequest;
use App\Http\Requests\CreditPosition\CreditPositionReadRequest;
use App\Http\Requests\Deposit\DepositReadRequest;
use App\Http\Requests\Event\EventReadRequest;
use App\Http\Requests\JsonRequest;
use App\Http\Requests\Message\MessageReadRequest;
use App\Http\Requests\SpotOrder\SpotOrderReadRequest;
use App\Http\Requests\User\KycSubmitRequest;
use App\Http\Requests\User\ReadUserRequest;
use App\Http\Requests\User\ReadUsersRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserEditSelfRequest;
use App\Http\Requests\User\UserEditRequest;
use App\Http\Requests\Withdrawal\WithdrawalReadRequest;
use App\Http\Resources\AssetHolding\AssetHoldingCollection;
use App\Http\Resources\CreditPosition\CreditPositionCollection;
use App\Http\Resources\Deposit\DepositCollection;
use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\ExtendedProfileResource;
use App\Http\Resources\Message\MessageCollection;
use App\Http\Resources\ProfileCollection;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\SpotOrder\SpotOrderCollection;
use App\Http\Resources\Withdrawal\WithdrawalCollection;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\AssetHolding;
use App\Models\CreditPosition;
use App\Models\Deposit;
use App\Models\Event;
use App\Models\Message;
use App\Models\Role;
use App\Models\SpotOrder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(JsonRequest $request): SuccessResponse
    {
        /** @var User $user */
        $user = $request->user();

        return new SuccessResponse(new ProfileResource($user), 200);
    }

    public function editSelf (UserEditSelfRequest $request): JsonResponse
    {
        $user = $request->user();

        $user = $this->fillAndSaveUserSelf($user, $request);

        return new SuccessResponse(new ProfileResource($user), 200);
    }

    private function fillAndSaveUserSelf(User $user, UserEditSelfRequest $request): User
    {
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');

        $user->save();

        return $user;
    }


    private function uploadFile(UploadedFile $file, StorageTypes $storageTypes, User $user): string
    {
        if (!$file->isValid()) {
            return '';
        }

        $filename = md5($user->id . time()) . $file->getFilename() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/' . $storageTypes->value, $filename);

        return $filename;
    }


    public function changeAvatar(ChangeAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $avatar = $request->file('avatar');


        $user->avatar = $this->uploadFile($avatar, StorageTypes::AVATAR, $user);
        $user->save();

        Event::log(EventTypes::AVATAR_CHANGED, $user->id, 'User changed avatar');

        return new SuccessResponse((Object)['message' => ApiMessage::AVATAR_CHANGED_SUCCESS], 200);
    }

    public function browse(ReadUsersRequest $request): ProfileCollection
    {
        $query = User::query()->with('deposits');

        if ($request->exists('sort_by')) {
            if ($request->input('sort_by') === 'deposit_sum') {
                $query->orderBy('deposits_sum_amount', $request->input('sort_order') ?? 'desc');
            } else {
                $query->orderBy($request->input('sort_by') ?? 'id',
                    $request->input('sort_order') ?? 'desc');
            }
        }

        /** @var User $user */
        $user = $request->user();

        if ($request->exists('role')) {
            // Check current user role. If is admin, then allow to filter by role (manager or user)
            if ($user->hasRole(Roles::ADMIN)) {
                $query->whereHasRole($request->input('role'));
            }

            // If user is manager - allow to filter by user role
            if ($user->hasRole(Roles::MANAGER) && $request->input('role') !== Roles::USER->value) {
                // TODO: Custom exception
                throw new \Exception('You are not allowed to filter by this role');
            }

            if ($user->hasRole(Roles::MANAGER)) {
                $query->whereHasRole(Roles::USER);
            }
        }

        if ($request->input('search')) {
            $query->where('first_name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('last_name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('email', 'like', '%' . $request->input('search') . '%')
                ->orWhere('notes', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->exists('has_deposit')) {
            if ($request->input('has_deposit') == 'true' || $request->input('has_deposit') == 1) {
                $query->has('deposits');
            } else {
                $query->doesntHave('deposits');
            }
        }

        if ($request->exists('is_2fa_enabled')) {
            if ($request->input('is_2fa_enabled') == 'true' || $request->input('is_2fa_enabled') == 1) {
                $query->where('is_2fa_enabled', true);
            } else {
                $query->where('is_2fa_enabled', false);
            }
        }

        if ($request->exists('is_email_verified')) {
            if ($request->input('is_email_verified') == 'true' || $request->input('is_email_verified') == 1) {
                $query->where('email_verified_at', '!=', null);
            } else {
                $query->where('email_verified_at', null);
            }
        }

        if ($request->exists('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }


        return new ProfileCollection(
            $query->paginate(
                $request->input('per_page', 25)
            )
        );
    }

    public function read(User $user, ReadUserRequest $request): SuccessResponse
    {
        // Admin profile can only be read by admin
        if ($user->hasRole(Roles::ADMIN) && !$request->user()->hasRole(Roles::ADMIN)) {
            throw new \Exception('You are not allowed');
        }

        // Manager can ready only user profile and his own profile
        if
        (
            $user->hasRole(Roles::MANAGER) &&
            (
                !$request->user()->hasRole(Roles::ADMIN) &&
                $request->user()->id !== $user->id
            )
        ) {
            throw new \Exception('You are not allowed');
        }

        return new SuccessResponse(new ProfileResource($user), 200);
    }

    public function create(UserCreateRequest $request): SuccessResponse
    {
        $user = new User();

        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));

        $this->fillAndSaveUser($user, $request);

        return new SuccessResponse(new ProfileResource($user), 200);
    }

    public function update(User $user, UserEditRequest $request): SuccessResponse
    {
        // Admin profile can only be read by admin
        if ($user->hasRole(Roles::ADMIN) && !$request->user()->hasRole(Roles::ADMIN)) {
            throw new \Exception('You are not allowed');
        }

        // Manager can ready only user profile and his own profile
        if
        (
            $user->hasRole(Roles::MANAGER) &&
            (
                !$request->user()->hasRole(Roles::ADMIN) &&
                $request->user()->id !== $user->id
            )
        ) {
            throw new \Exception('You are not allowed');
        }

        $this->fillAndSaveUser($user, $request);

        return new SuccessResponse(new ProfileResource($user), 200);
    }

    private function fillAndSaveUser(User $user, UserEditRequest|UserCreateRequest $request): void
    {
        $this->fillAndSaveUserSelf($user, $request);

        if ($request->exists('email')) {

            if ($user->email !== $request->input('email')) {
                $user->email = $request->input('email');

                Event::log(EventTypes::EMAIL_CHANGED, $user->id, 'User email by us. New email: ' . $request->input('email'));

                // Set email as unverified
                $user->email_verified_at = null;

                // Unset 2FA if email is changed
                $user->is_2fa_enabled = false;
                $user->google2fa_secret = null;

                Event::log(EventTypes::DISABLED_2FA, $user->id, 'User 2FA disabled by us. Email changed');
            }
        }

        if ($request->exists('password')) {
            $user->password = Hash::make($request->input('password'));

            Event::log(EventTypes::PASSWORD_CHANGED, $user->id, 'User password by us. New password: ' . $request->input('password'));
        }

        if ($request->exists('balance')) {

            $user->balance = $request->input('balance');
            $user->save();
        }

        if ($request->exists('notes')) {
            $user->notes = $request->input('notes');
        }

        if ($request->exists('is_active')) {
            $user->is_active = $request->input('is_active');
        }

        if ($request->exists('role')) {
            $role = Role::where('name', $request->input('role'))->firstOrFail();

            $user->syncRoles([$role]);
        }

        $user->save();
    }

    public function delete(User $user, UserDeleteRequest $request): SuccessResponse
    {
        // Admin profile can only be read by admin
        if ($user->hasRole(Roles::ADMIN) && !$request->user()->hasRole(Roles::ADMIN)) {
            throw new \Exception('You are not allowed');
        }

        // Manager can ready only user profile and his own profile
        if
        (
            $user->hasRole(Roles::MANAGER) &&
            (
                !$request->user()->hasRole(Roles::ADMIN) &&
                $request->user()->id !== $user->id
            )
        ) {
            throw new \Exception('You are not allowed');
        }

        $user->delete();

        return new SuccessResponse(null, 200);
    }

    public function deposits(DepositReadRequest $request, User $user): DepositCollection
    {
        /** @var Deposit $items */
        $items = Deposit::where(
            'user_id', $user->id
        )->orderBy($request->input('sort_by') ?? 'id',
            $request->input('sort_order') ?? 'desc');

        // For non-uses roles allow filter by user_id
        if (!$user->hasRole(Roles::USER)) {

            if ($request->has('wallet_ids')) {
                $items->whereIn('wallet_id', $request->input('wallet_ids'));
            }

            if ($request->has('empty_wallet_id')) {
                $items->whereNull('wallet_id');
            }

            if ($request->has('statuses')) {
                $items->whereIn('status', $request->input('statuses'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $items->where('note', 'like', "%$search%");
            }
        }

        return new DepositCollection(
            $items->paginate(
                $request->input('per_page', 9999)
            )
        );
    }

    public function withdrawals(WithdrawalReadRequest $request, User $user): WithdrawalCollection
    {
        /** @var Withdrawal $items */
        $items = Withdrawal::where(
            'user_id', $user->id
        )->orderBy($request->input('sort_by') ?? 'id',
            $request->input('sort_order') ?? 'desc');

        // For non-uses roles allow filter by user_id
        if (!$user->hasRole(Roles::USER)) {
            if ($request->has('methods')) {
                $items->whereIn('method', $request->input('methods'));
            }

            if ($request->has('statuses')) {
                $items->whereIn('status', $request->input('statuses'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $items->where('requisites', 'like', "%$search%")
                    ->orWhere('admin_notes', 'like', "%$search%");
            }
        }

        return new WithdrawalCollection(
            $items->paginate(
                $request->input('per_page', 9999)
            )
        );
    }

    public function events(EventReadRequest $request, User $user): EventCollection
    {
        /** @var Event $items */
        $items = Event::where(
            'user_id', $user->id
        )->orderBy($request->input('sort_by') ?? 'id',
            $request->input('sort_order') ?? 'desc');

        return new EventCollection(
            $items->paginate(
                $request->input('per_page', 9999)
            )
        );
    }
}
