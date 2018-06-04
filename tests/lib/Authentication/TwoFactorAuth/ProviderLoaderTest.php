<?php
/**
 * Created by PhpStorm.
 * User: christoph
 * Date: 04.06.18
 * Time: 13:29
 */

namespace lib\Authentication\TwoFactorAuth;

use OC\Authentication\TwoFactorAuth\ProviderLoader;

class ProviderLoaderTest extends \Test\TestCase {

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Could not load two-factor auth provider \OCA\MyFaulty2faApp\DoesNotExist
	 */
	public function testFailHardIfProviderCanNotBeLoaded() {
		$this->providerLoader->expects($this->once())
			->method('getProviders')
			->with($this->user)
			->will($this->returnValue(['faulty2faapp']));
		$this->manager->expects($this->once())
			->method('loadTwoFactorApp')
			->with('faulty2faapp');

		$this->providerLoader->expects($this->once())
			->method('getAppInfo')
			->with('faulty2faapp')
			->will($this->returnValue([
				'two-factor-providers' => [
					'\OCA\MyFaulty2faApp\DoesNotExist',
				],
			]));

		$this->manager->getProviderSet($this->user);
	}

}
