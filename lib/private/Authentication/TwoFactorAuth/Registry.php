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

namespace OC\Authentication\TwoFactorAuth;

use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\Authentication\TwoFactorAuth\IStatefulProvider;
use OCP\IUser;

class Registry implements IRegistry {

	public function isTwoFactorEnabledFor(IUser $user): bool {
		// TODO
		return false;
	}

	public function isProviderEnabledFor(IStatefulProvider $provider, IUser $user): bool {
		// TODO
		return false;
	}

	public function enableProviderFor(IStatefulProvider $provider, IUser $user) {
		// TODO
	}

	public function disableProviderFor(IStatefulProvider $provider, IUser $user) {
		// TODO
	}

}
