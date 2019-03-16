<?php
declare(strict_types=1);

namespace App\PlayerAccountManagement\Infrastructure\Doctrine\Adapter;

use App\PlayerAccountManagement\Domain\Entity\Player;
use App\PlayerAccountManagement\Domain\Port\PlayerReader;
use App\PlayerAccountManagement\Domain\Port\PlayerWriter;
use App\PlayerAccountManagement\Domain\ValueObject\Email;
use App\PlayerAccountManagement\Domain\ValueObject\PlayerId;
use App\PlayerAccountManagement\Domain\ValueObject\PlayerName;
use App\Shared\Infrastructure\Doctrine\Exception\NotFoundException;
use App\PlayerAccountManagement\Infrastructure\Doctrine\Exception\ErrorMessage;
use App\PlayerAccountManagement\Infrastructure\Doctrine\PlayerDataMapper;
use App\Shared\Infrastructure\Exception\InternalException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Uuid;

final class PlayerRepository extends ServiceEntityRepository implements PlayerReader, PlayerWriter
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerDataMapper::class);
    }

    public function isEmailAlreadyUsed(Email $email): bool
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.email')
            ->where('p.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->getResult();

        return (!empty($result));
    }

    public function isNameAlreadyUsed(PlayerName $playerName): bool
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.name')
            ->where('p.name = :name')
            ->setParameter('name', (string) $playerName)
            ->getQuery()
            ->getResult();

        return (!empty($result));
    }

    /**
     * @throws InternalException
     * @throws NotFoundException
     */
    public function get(PlayerId $playerId): Player
    {
        try {
            /** @var PlayerDataMapper $playerDataMapper */
            $playerDataMapper = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.id = :id')
                ->setParameter('id', Uuid::fromString((string) $playerId))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new InternalException($e->getMessage(), $e->getCode(), $e);
        }

        if (empty($playerDataMapper)) {
            throw new NotFoundException((string) ErrorMessage::PLAYER_NOT_FOUND());
        }

        return $playerDataMapper->toPlayer();
    }

    /**
     * @throws InternalException
     */
    public function add(Player $player): void
    {
        $playerDataMapper = new PlayerDataMapper($player);

        try {
            $this->_em->persist($playerDataMapper);
            $this->_em->flush($playerDataMapper);
        } catch (ORMException $e) {
            throw new InternalException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws InternalException
     * @throws NotFoundException
     */
    public function findWithEmail(Email $email): Player
    {
        try {
            /** @var PlayerDataMapper $playerDataMapper */
            $playerDataMapper = $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.email = :email')
                ->setParameter('email', (string) $email)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new InternalException($e->getMessage(), $e->getCode(), $e);
        }

        if (empty($playerDataMapper)) {
            throw new NotFoundException((string) ErrorMessage::PLAYER_NOT_FOUND());
        }

        return $playerDataMapper->toPlayer();
    }
}