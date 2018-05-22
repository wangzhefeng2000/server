<?php

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Core\Command\TwoFactorAuth;

use OC\Authentication\TwoFactorAuth\Manager as TwoFactorManager;
use OC\Core\Command\Base;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\Authentication\TwoFactorAuth\IStatefulProvider;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class State extends Base {

	/** @var IRegistry */
	private $registry;

	/** @var IUserManager */
	private $userManager;

	/** @var TwoFactorManager */
	private $twoFactorManager;

	public function __construct(IRegistry $registry, IUserManager $userManager,
		TwoFactorManager $twoFactorManager) {
		parent::__construct('twofactorauth:state');
		$this->userManager = $userManager;
		$this->registry = $registry;
		$this->twoFactorManager = $twoFactorManager;
	}

	protected function configure() {
		parent::configure();

		$this->setName('twofactorauth:state');
		$this->setDescription('Get the two-factor authentication (2FA) state of a user');
		$this->addArgument('uid', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$uid = $input->getArgument('uid');
		$user = $this->userManager->get($uid);
		if (is_null($user)) {
			$output->writeln("<error>Invalid UID</error>");
			return;
		}
		if ($this->registry->isTwoFactorEnabledFor($user)) {
			$output->writeln("Two-factor authentication is enabled for user $uid");
		} else {
			$output->writeln("Two-factor authentication is not enabled for user $uid");
		}

		$output->writeln("");

		list ($enabled, $disabled, $unknown) = $this->filterEnabledDisabledUnknownProviders($user,
			$this->twoFactorManager->getProviders($user, true, false));

		$this->printProviders("Enabled providers", $enabled, $output);
		$this->printProviders("Disabled providers", $disabled, $output);
		$this->printProviders("Legacy providers with unknown state", $unknown, $output);
	}

	private function filterEnabledDisabledUnknownProviders(IUser $user,
		array $providers): array {
		$enabled = [];
		$disabled = [];
		$unknown = [];

		foreach ($providers as $provider) {
			/* @var $provider IProvider */
			if ($provider instanceof IStatefulProvider) {
				if ($this->registry->isProviderEnabledFor($provider, $user)) {
					$enabled[] = $provider;
				} else {
					$disabled[] = $provider;
				}
			} else {
				$unknown[] = $provider;
			}
		}

		return [$enabled, $disabled, $unknown];
	}

	private function printProviders(string $title, array $providers,
		OutputInterface $output) {
		if (!empty($providers)) {
			$output->writeln($title . ":");
			foreach ($providers as $provider) {
				$output->writeln("- " . $provider->getId() . " (" . $provider->getDescription() . ")");
			}
		}
	}

}
