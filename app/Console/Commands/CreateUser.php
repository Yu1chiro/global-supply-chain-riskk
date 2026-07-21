<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// Kelas CreateUser: create user
class CreateUser extends Command
{
    protected $signature = 'user:create
        {--name= : Nama lengkap}
        {--email= : Email untuk login}
        {--password= : Password (min 8 karakter)}';

    protected $description = 'Buat akun login baru secara manual (dipakai karena form register publik ditutup)';

    // handle
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 karakter)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Akun berhasil dibuat: {$user->email} (id: {$user->id})");
        return self::SUCCESS;
    }
}
