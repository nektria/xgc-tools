<?php

declare(strict_types=1);

namespace Xgc\DB;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use Throwable;
use Xgc\Dto\Clock;
use Xgc\Dto\LocalClock;
use Xgc\Exception\BaseException;

use function count;

/**
 * @template T of EntityInterface
 */
abstract class WriteModel
{
    protected private(set) EntityManager $manager;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry
    ) {
        $manager = $this->managerRegistry->getManager();
        if (!($manager instanceof EntityManager)) {
            throw new BaseException('Unable to restart the manager.');
        }
        $this->manager = $manager;
    }

    /**
     * @return string[]
     */
    public function debugChanges(EntityInterface $domain): array
    {
        $unitOfWork = $this->manager()->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($domain);
        $list = [];

        $persistenceType = PersistenceType::None;
        if (($originalData['id'] ?? null) === null) {
            $persistenceType = PersistenceType::New;
        }

        if ($persistenceType === PersistenceType::None) {
            $reflector = new ReflectionClass($domain);
            $properties = $reflector->getProperties();
            foreach ($properties as $property) {
                $name = $property->getName();

                if (count($property->getAttributes(IgnoreProperty::class)) > 0) {
                    continue;
                }

                $value = $property->getValue($domain);
                $originalValue = $originalData[$name] ?? null;

                if ($value instanceof Clock || $value instanceof LocalClock) {
                    $value = $value->dateTimeString();
                }

                if ($originalValue instanceof Clock || $originalValue instanceof LocalClock) {
                    $originalValue = $originalValue->dateTimeString();
                }

                if ($originalValue !== $value) {
                    if (count($property->getAttributes(HardProperty::class)) > 0) {
                        $list[] = $name;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @param T $domain
     */
    public function getPersistenceType(EntityInterface $domain): PersistenceType
    {
        $unitOfWork = $this->manager()->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($domain);

        $persistenceType = PersistenceType::None;
        if (($originalData['id'] ?? null) === null) {
            $persistenceType = PersistenceType::New;
        }

        if ($persistenceType === PersistenceType::None) {
            $reflector = new ReflectionClass($domain);
            $properties = $reflector->getProperties();
            foreach ($properties as $property) {
                $name = $property->getName();

                if (count($property->getAttributes(IgnoreProperty::class)) > 0) {
                    continue;
                }

                $value = $property->getValue($domain);
                $originalValue = $originalData[$name] ?? null;

                if ($value instanceof Clock || $value instanceof LocalClock) {
                    $value = $value->dateTimeString();
                }

                if ($originalValue instanceof Clock || $originalValue instanceof LocalClock) {
                    $originalValue = $originalValue->dateTimeString();
                }

                if ($originalValue !== $value) {
                    if (count($property->getAttributes(HardProperty::class)) > 0) {
                        $persistenceType = PersistenceType::HardUpdate;

                        break;
                    }
                    $persistenceType = PersistenceType::SoftUpdate;
                }
            }
        }

        return $persistenceType;
    }

    public function manager(): EntityManager
    {
        return $this->manager;
    }

    /**
     * @param T $domain
     */
    protected function deleteEntity(EntityInterface $domain): void
    {
        $this->checkFromService();

        try {
            $this->manager->remove($domain);
            $this->manager->flush();
        } catch (Throwable) {
            $domain = $this->findEntity($domain->id());
            if ($domain !== null) {
                $this->manager->remove($domain);
                $this->manager->flush();
            }
        }
    }

    /**
     * @param mixed[] $criteria
     * @param mixed[]|null $orderBy
     * @return T[]
     */
    protected function findBy(array $criteria, ?array $orderBy = null, int $limit = 10000, int $offset = 0): array
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @return T|null
     */
    protected function findEntity(string $id): ?EntityInterface
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param mixed[] $criteria
     * @return T|null
     */
    protected function findOneBy(array $criteria): ?EntityInterface
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getClassName(): string;

    /**
     * @return EntityRepository<T>
     */
    protected function getRepository(): EntityRepository
    {
        return $this->manager->getRepository($this->getClassName());
    }

    /**
     * @param T $domain
     */
    protected function saveEntity(EntityInterface $domain): void
    {
        $this->checkFromService();
        $persistenceType = $this->getPersistenceType($domain);
        if ($persistenceType === PersistenceType::None) {
            return;
        }

        try {
            if ($persistenceType === PersistenceType::HardUpdate) {
                $domain->refresh();
            }
            $this->manager->persist($domain);
            $this->manager->flush();
            $this->manager->detach($domain);

            return;
        } catch (Throwable $e) {
            $this->resetManager();

            if (
                $domain instanceof EventEntity
                && str_contains($e->getMessage(), 'duplicate key value violates unique constraint')
            ) {
                try {
                    $domain->fixTimeStamp();
                    $this->manager->persist($domain);
                    $this->manager->flush();
                    $this->manager->detach($domain);

                    return;
                } catch (Throwable) {
                    $this->resetManager();

                    throw BaseException::extend($e);
                }
            }

            throw BaseException::extend($e);
        }
    }

    private function checkFromService(): void
    {
        $o1 = debug_backtrace()[1]['object'] ?? null;
        if ($o1 === null) {
            throw new BaseException('Unable to check the called.');
        }
        $p1 = explode('\\', $o1::class);
        $resource1 = explode('WriteModel', end($p1))[0];

        $o2 = debug_backtrace()[3]['object'] ?? null;
        if ($o2 === null) {
            throw new BaseException('Unable to check the caller.');
        }
        $p2 = explode('\\', $o2::class);
        $resource2 = end($p2);

        if ($resource2 === "{$resource1}Service") {
            return;
        }

        throw new BaseException("You can only call save or delete from '{$resource1}Service.'");
    }

    private function resetManager(): void
    {
        try {
            if ($this->manager->getConnection()->isTransactionActive()) {
                $this->manager->getConnection()->rollBack();
            }

            $this->managerRegistry->resetManager();
            $manager = $this->managerRegistry->getManager();
            if (!($manager instanceof EntityManager)) {
                throw new BaseException('Unable to restart the manager.');
            }

            $this->manager = $manager;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }
}
