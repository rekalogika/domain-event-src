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

namespace Rekalogika\DomainEvent\Outbox\Command;

use Rekalogika\DomainEvent\Outbox\MessageRelayInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MessageRelayCommand extends Command
{
    public function __construct(
        private string $defaultManagerName,
        private MessageRelayInterface $messageRelay,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'managerName',
            mode: InputArgument::OPTIONAL,
            description: 'The name of the entity manager to relay messages from.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $managerName = $input->getArgument('managerName') ?? $this->defaultManagerName;

        if (!is_string($managerName)) {
            throw new \InvalidArgumentException('The manager name must be a string.');
        }

        $this->messageRelay->relayMessages($managerName);

        return Command::SUCCESS;
    }
}
