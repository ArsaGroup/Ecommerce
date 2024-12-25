<?php

namespace App\Http\Interfaces;

interface AuthInterface
{
    public function login(array $data);

    public function register(array $data);

    public function logout();
}
