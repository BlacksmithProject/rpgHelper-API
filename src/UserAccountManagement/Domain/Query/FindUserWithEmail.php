<?php
declare(strict_types=1);

namespace App\UserAccountManagement\Domain\Query;

use App\UserAccountManagement\Domain\Entity\User;
use App\UserAccountManagement\Domain\Port\UserReader;
use App\UserAccountManagement\Domain\ValueObject\Email;

final class FindUserWithEmail
{
    private $userReader;

    public function __construct(UserReader $userReader)
    {
        $this->userReader = $userReader;
    }

    public function __invoke(Email $email): User
    {
        return $this->userReader->findWithEmail($email);
    }
}
