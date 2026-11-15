<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Users\CreateAdministrator;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:create-admin')]
#[Description('Create an administrator account')]
final class CreateAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CreateAdministrator $createAdministrator): int
    {
        $firstName = text(
            label: 'First name',
            required: true,
            validate: ['first_name' => ['max:255']],
        );

        $lastName = text(
            label: 'Last name',
            required: true,
            validate: ['last_name' => ['max:255']],
        );

        $email = text(
            label: 'Email',
            required: true,
            validate: ['email' => ['email', 'max:255', Rule::unique(User::class)]],
        );

        $password = password(
            label: 'Password',
            required: true,
            validate: ['password' => [Password::defaults()]],
        );

        $user = $createAdministrator->handle($firstName, $lastName, $email, $password);

        $this->info("Administrator {$user->email} has been created.");

        return self::SUCCESS;
    }
}
