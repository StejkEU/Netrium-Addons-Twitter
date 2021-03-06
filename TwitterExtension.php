<?php

/**
 * This file is part of the Netrium Framework
 *
 * Copyright (c) 2014 Martin Sadovy (http://sadovy.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Netrium\Addons\Twitter;

use Nette;
use Nette\DI;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
}

/**
 * Twitter extension
 *
 * @author Martin Sadovy
 */
class TwitterExtension extends DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig(array(
			'authenticator.sessionNamespace' => 'Twitter'
		));
		if (!isset($config['consumerKey']) || !isset($config['consumerSecretKey'])) {
			throw new Nette\InvalidArgumentException("Twitter extension requries 'consumerKey' and 'consumerSecretKey' parameters.");
		}

		$builder = $this->getContainerBuilder();

		$api = $builder->addDefinition($this->prefix('api'))
				->setClass('TwitterOAuth', array(
					$config['consumerKey'],
					$config['consumerSecretKey']
				))->setShared(FALSE);

		if (isset($config['accessKey']) && isset($config['accessSecret'])) {
			$api->addSetup('setOAuthToken', array(
				$config['accessKey'],
				$config['accessSecret']
			));
		}

		$builder->addDefinition($this->prefix('authenticator.storage'))
			->setClass('Netrium\Addons\Twitter\SessionStorage')
			->setFactory(get_called_class() . '::createSessionStorage', array('@session', $config['authenticator.sessionNamespace']));

		$builder->addDefinition($this->prefix('authenticator'))
			->setClass('Netrium\Addons\Twitter\Authenticator', array(
				'@' . $this->prefix('api'),
			));
	}

	public static function createSessionStorage($session, $name)
	{
		return new SessionStorage($session->getSection($name));
	}

}
