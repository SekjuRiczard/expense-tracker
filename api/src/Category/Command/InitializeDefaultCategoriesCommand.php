<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Category\Command;

use App\Category\Service\DefaultCategoryInitializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:category:initialize-defaults-categories',
    description: 'Initializes default transaction categories.',
)]
final class InitializeDefaultCategoriesCommand extends Command
{
    public function __construct(private readonly DefaultCategoryInitializer $defaultCategoryInitializer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf(
            'Default categories initialized. Created: %d.',
            $this->defaultCategoryInitializer->initialize(),
        ));

        return Command::SUCCESS;
    }
}
