<?php
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
declare(strict_types=1);

namespace OCP\AppFramework;

use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;

/**
 * Base controller for interactive public shares
 *
 * It will verify if the user is properly authenticated to the share. If not the
 * user will be redirected to an authentication page.
 *
 * Use this for a controller that is to be called directly by a user. So the
 * normal public share page for files/calendars etc.
 */
abstract class AuthPublicShareController extends PublicShareController {

	/** @var IURLGenerator */
	protected $urlGenerator;

	public function __construct(string $appName,
								IRequest $request,
								ISession $session,
								IURLGenerator $urlGenerator) {
		parent::__construct($appName, $request, $session);

		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Show the authentication page
	 * The form has to submit to the authenticate method route
	 */
	abstract public function showAuthenticate(): TemplateResponse;

	/**
	 * The template to show when authentication failed
	 */
	abstract protected function showAuthFailed(): TemplateResponse;

	/**
	 * Verify the password
	 */
	abstract protected function verifyPassword(string $password): bool;

	/**
	 * Function called after failed authentication
	 *
	 * You can use this to do some logging for example
	 */
	protected function authFailed() {
	}

	/**
	 * Function called after successfull authentication
	 *
	 * You can use this to do some logging for example
	 */
	protected function authSucceeded() {
	}

	/**
	 * @UseSession
	 * @PublicPage
	 * @BruteForceProtection(action=publicLinkAuth)
	 *
	 * Authenticate the share
	 */
	final public function authenticate(string $password = '') {
		// Already authenticated
		if ($this->isAuthenticated()) {
			return $this->getRedirect();
		}

		if (!$this->verifyPassword($password)) {
			$this->authFailed();
			$response = $this->showAuthFailed();
			$response->throttle();
			return $response;
		}

		$this->session->regenerateId();
		$response = $this->getRedirect();

		$this->session->clear();
		$this->session->set('public_link_authenticated_token', $this->getToken());
		$this->session->set('public_link_authenticated_password_hash', $this->getPasswordHash());

		$this->authSucceeded();

		return $response;
	}

	/**
	 * Default landing page
	 */
	abstract public function show(): TemplateResponse;

	final public function getAuthenticationRedirect(string $redirect): RedirectResponse {
		return new RedirectResponse(
			$this->urlGenerator->linkToRoute($this->getRoute('showAuthenticate'), ['token' => $this->getToken(), 'redirect' => $redirect])
		);
	}

	private function getRoute(string $function): string {
		$app = strtolower($this->appName);
		$class = strtolower((new \ReflectionClass($this))->getShortName());

		return $app . '.' . $class . '.' . $function;
	}

	private function getRedirect(): RedirectResponse {
		//Get all the stored redirect parameters:
		$params = $this->session->get('public_link_authenticate_redirect');

		$route = $this->getRoute('show');

		if ($params === null) {
			$params = [];
		} else {
			$params = json_decode($params, true);
			if (isset($params['_route'])) {
				$route = $params['_route'];
				unset($params['_route']);
			}
		}

		return new RedirectResponse($this->urlGenerator->linkToRoute($route, $params));
	}
}
