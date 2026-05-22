<?php
declare(strict_types=1);

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMahasiswa(): bool
{
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'mahasiswa' || $_SESSION['role'] === 'user');
}

