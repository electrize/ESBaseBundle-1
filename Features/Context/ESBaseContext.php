<?php


namespace ES\Bundle\BaseBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityRepository;
use ES\Bundle\BaseBundle\Tests\Utils;
use Symfony\Component\HttpKernel\KernelInterface;

class ESBaseContext extends MinkContext implements KernelAwareContext
{
	protected $kernel;

	public function setKernel(KernelInterface $kernel)
	{
		$this->kernel = $kernel;
	}

	/**
	 * Login staging.
	 *
	 * @Given /^(?:|I )have staging access$/
	 */
	public function iHaveStagingAccess()
	{
		$this->getSession()->visit($this->locatePath('/'));
		list($name, $value) = Utils::getStagingCookie();
		$this->getSession()->setCookie($name, $value);
	}

	/**
	 * Reset current session.
	 *
	 * @Given /^(?:|I )have a new session$/
	 */
	public function resetSession()
	{
		$this->getSession()->reset();
	}

	/**
	 * Get repository of given entity
	 *
	 * @param string $entity
	 * @return EntityRepository
	 */
	protected function getRepository($entity)
	{
		return $this->kernel->getContainer()->get('doctrine')->getRepository($entity);
	}
} 