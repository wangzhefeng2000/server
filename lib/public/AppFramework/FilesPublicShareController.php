<?php
declare(strict_types=1);
/**
 * @copyright 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCP\AppFramework;

use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager as ShareManager;
use OCP\Share\IShare;

abstract class FilesPublicShareController extends AuthPublicShareController {

	/** @var ShareManager */
	protected $shareManager;

	/** @var IShare */
	protected $share;

	public function __construct(string $appName,
								IRequest $request,
								ISession $session,
								IURLGenerator $urlGenerator,
								ShareManager $shareManager) {
		parent::__construct($appName, $request, $session, $urlGenerator);

		$this->shareManager = $shareManager;
	}

	protected function verifyPassword(string $password): bool {
		return $this->shareManager->checkPassword($this->share, $password);
	}

	protected function getPasswordHash(): string {
		return $this->share->getPassword();
	}

	public function isValidToken(): bool {
		try {
			$this->share = $this->shareManager->getShareByToken($this->getToken());
		} catch (ShareNotFound $e) {
			return false;
		}

		return true;
	}

	protected function isPasswordProtected(): bool {
		return $this->share->getPassword() !== null;
	}

	protected function authSucceeded() {
		// For share this was always set so it is still used in other apps
		$this->session->set('public_link_authenticated', (string)$this->share->getId());
	}

}
