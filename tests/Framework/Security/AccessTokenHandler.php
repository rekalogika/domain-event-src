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

namespace Rekalogika\DomainEvent\Tests\Framework\Security;

use Rekalogika\DomainEvent\Tests\Framework\Entity\User;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    #[\Override]
    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        return match ($accessToken) {
            'user' => new UserBadge(
                'user',
                static fn(string $userIdentifier): User => new User(),
            ),
            default => throw new BadCredentialsException('Invalid credentials.'),
        };
    }
}
