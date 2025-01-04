<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/domain-event-src package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\DomainEvent\Tests\Framework\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @param non-empty-string $username
     * @param list<string> $roles
     */
    public function __construct(
        private readonly string $username = 'user',
        private readonly array $roles = ['ROLE_USER'],
    ) {}

    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    #[\Override]
    public function eraseCredentials(): void {}

    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
