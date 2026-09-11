<?php

namespace App\Console\Commands;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('admin:create')]
#[Description('Create a Jelajah Jabar admin using a securely prompted password')]
class CreateAdmin extends Command
{
    use ProfileValidationRules;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Run this command interactively to enter a hidden password.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Name'));
        $email = Str::lower(trim((string) $this->ask('Email')));
        $profileValidator = Validator::make(['name' => $name, 'email' => $email], $this->profileRules());

        if ($profileValidator->fails()) {
            foreach ($profileValidator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Password', fallback: false);
        $passwordConfirmation = $this->secret('Confirm password', fallback: false);
        $passwordValidator = Validator::make([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = (string) $password;
        $user->is_admin = true;
        $user->save();

        $this->info('Admin created successfully.');

        return self::SUCCESS;
    }
}
