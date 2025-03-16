<?php

namespace Beam\Beam\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Beam\BeamCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'ds:init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected $signature = 'ds:init {pwd=0}';

    protected $description = 'Beam Init';

    public function handle(): void
    {
        /** @var string $pwd */
        $pwd = $this->argument('pwd');

        if ($pwd == "0" && isset($_ENV['IGNITION_LOCAL_SITES_PATH'])) {
            $pwd = $_ENV['IGNITION_LOCAL_SITES_PATH'];
        }

        if (Config::exists()) {
            ds('Welcome back to the Beam!');

            $this->components->info('beam.yaml has already been published');

            return;
        }

        $defaultYaml = appBasePath() . 'vendor/palzin/beam-core/src/Commands/beam-base.yaml';

        $publish = Config::publish(
            pwd: $pwd . DIRECTORY_SEPARATOR,
            filepath: $defaultYaml
        );

        $newYaml = appBasePath() . 'beam.yaml';

        if ($publish) {
            /** @var array $yamlFile */
            $yamlFile = Yaml::parseFile(__DIR__ . '/beam-base.yaml');
            /** @var array $default */
            $default = Yaml::parseFile($defaultYaml);

            foreach ($default as $key => $values) {
                /**
                 * @var string $key1
                 * @var array $values
                 */
                foreach ($values as $key1 => $value) {
                    $default[$key][$key1] = $value;
                }
            }

            $yamlFile['app']['project_path'] = $pwd . DIRECTORY_SEPARATOR;

            $mergedYaml = array_replace_recursive($default, $yamlFile);

            $yaml = Yaml::dump($mergedYaml);
            file_put_contents($newYaml, $yaml);

            $this->sendMessageToApp();

            $this->components->info('The beam.yaml file was published in <comment>' . $pwd . '</comment>');
            $this->components->info('Read the docs: https://palzin.app/beam/docs');

            Process::run('echo "beam.yaml" >> .gitignore');

            return;
        };
    }

    private function sendMessageToApp(): void
    {
        ds('Welcome to the Beam!');
    }
}
