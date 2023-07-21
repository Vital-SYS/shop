<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create';

    protected $description = 'Создание пользователя-админа';

    public function handle()
    {
        $name = $this->ask('Введите ваше имя:');
        $email = $this->ask('Введите ваш email:');
        $password = $this->secret('Введите ваш пароль:');

        // Создание пользователя
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->admin = 1;
        $user->save();

        $this->info('Пользователь успешно создан!');
    }
}
