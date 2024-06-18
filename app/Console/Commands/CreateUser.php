<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firstName = $this->ask('Enter first name:');
        $lastName = $this->ask('Enter last name:');
        $email = $this->ask('Enter email:');
        $password = $this->secret('Enter password:');

        // Create the user
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Print all roles
        $roles = Role::all()->pluck('name');
        $this->info('Available roles: ' . $roles->implode(', '));

        // Ask for the role
        $roleName = $this->ask('Enter role name:');

        // Check if the role exists
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            // Attach the role to the user
            $user->addRole($role);
            $this->info('Role assigned successfully!');
        } else {
            $this->error('Role does not exist!');
        }

        $this->info('User created successfully!');

        return 0;
    }
}
