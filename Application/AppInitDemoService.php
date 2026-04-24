<?php

namespace App\Plugins\Demo\Application;

use App\Core\Console\AbstractConsoleCommandService;
use App\Modules\Cave\Application\Write\Dto\CaveWriteDto;
use App\Modules\Cave\Application\Write\Handler\CreateCaveHandler;
use App\Modules\Cave\Domain\Cave;
use App\Modules\Plugin\Application\PluginManager\Write\Handler\SyncPluginsHandler;
use App\Modules\Plugin\Domain\CavePlugin;
use App\Modules\Plugin\Infrastructure\Repository\PluginRepository;
use App\Modules\User\Application\User\Write\Dto\UserWriteDto;
use App\Modules\User\Application\User\Write\Handler\CreateUserHandler;
use App\Modules\User\Domain\User;
use App\Shared\Security\ActorContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppInitDemoService extends AbstractConsoleCommandService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CreateUserHandler $createUserHandler,
        private readonly CreateCaveHandler $createCaveHandler,
        private readonly PluginRepository $pluginRepository,
        private readonly SyncPluginsHandler $syncPluginsHandler,
    ) {}

    public function init(SymfonyStyle $io)
    {
        $this->io = $io;
        $cave = $this->createDemoCave();
        $this->createDemoAdmin($cave);
        $this->activatePluginsForCave($cave);
    }

    private function syncPlugins(): void
    {
        $this->setTitle('Syncing Plugins');
        $this->syncPluginsHandler->handle();
    }

    private function createDemoCave(): Cave
    {
        $this->setTitle('Creating Demo Cave');
        $cave = $this->em->getRepository(Cave::class)->findOneBy(['slug' => 'demo']);
        if (!$cave) {
            $dto = new CaveWriteDto(
                name: 'Demo',
                slug: 'demo',
                active: true,
            );
            $cave = $this->createCaveHandler->handle($dto);
            $this->setLine('Created demo cave', true);
        } else {
            $this->setLine('Updating demo cave', true);
        }

        $cave->setSettings([
            'inscriptionEnabled' => true,
            'ticket_provider' => 'MidiMesureTicketProviderService',
        ]);

        $this->em->persist($cave);
        $this->em->flush();

        return $cave;
    }

    private function createDemoAdmin(Cave $cave): void
    {
        $this->setTitle('Creating Demo Admin User and Cave');
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'demo@vitilog.fr']);
        if (!$user) {
            $userDto = new UserWriteDto(
                email: 'demo@vitilog.fr',
                roles: ['ROLE_CAVE_ADMIN'],
                code: 'demo',
                nom: 'Demo',
                prenom: 'Admin',
                societe: 'Vitilog',
                password: 'demo',
            );
            $user = $this->createUserHandler->handle(
                new ActorContext(userId: 0, roles: ['ROLE_SUPERADMIN']),
                $cave,
                $userDto
            );
            $user->setRoles(['ROLE_CAVE_ADMIN']);

            $this->em->persist($user);
            $this->em->flush();
        }
    }

    private function activatePluginsForCave(Cave $cave): void
    {
        // Activate all plugins for superadmin
        $plugins = $this->pluginRepository->findAll();
        foreach ($plugins as $plugin) {
            // Activate plugin
            $plugin->setActive(true);
            $this->pluginRepository->save($plugin);
            if (!$plugin->isInstalled()) {
                continue;
            }
            $this->setLine('Plugin ' . $plugin->getName() . ' activated', true);

            // Create cave plugin entity
            $cavePlugin = $this->em->getRepository(CavePlugin::class)->findOneBy(['cave' => $cave, 'plugin' => $plugin]);
            if (!$cavePlugin) {
                $cavePlugin = new CavePlugin();
                $cavePlugin->setCave($cave);
                $cavePlugin->setPlugin($plugin);
                $cavePlugin->setEnabled(true);
                $this->em->persist($cavePlugin);
                $this->em->flush();
            }
            $cavePlugin->setEnabled(true);
            $this->em->persist($cavePlugin);
            $this->em->flush();
            $this->setLine('Plugin ' . $plugin->getName() . ' activated for demo cave', true);
        }
    }
}
