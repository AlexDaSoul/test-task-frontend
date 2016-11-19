<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadUserData.
 */
class LoadUserData implements FixtureInterface
{
    /**
     * Load data fixtures
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setIdToken('5dsf4dsf5sdf4');
        $user->setUserName('Test User');
        $user->setUserEmail('test@test.com');

        $manager->persist($user);
        $manager->flush();
    }
}
