<?php

namespace lingyun\console\command;

use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use lingyun\console\trait\InteractsWithIO;
use lingyun\events\AssetsTagPublished;
use lingyun\support\ServiceProvider;
use think\console\Command;
use think\console\input\Option;
use think\helper\Arr;

class AssetsPublishCommand extends Command
{
    use InteractsWithIO;
    /**
     * The provider to publish.
     *
     * @var string
     */
    protected $provider = null;

    /**
     * The tags to publish.
     *
     * @var array
     */
    protected $tags = [];

    public function configure()
    {
        $this->setName('assets:publish')
            ->addOption('force', 'f', Option::VALUE_NONE, 'Overwrite any existing files')
            ->addOption('existing', null, Option::VALUE_NONE, 'Publish and overwrite only the files that have already been published')
            ->addOption('all', null, Option::VALUE_NONE, 'Publish assets for all service providers without prompt')
            ->addOption('provider', null, Option::VALUE_OPTIONAL, 'The service provider that has assets you want to publish')
            ->addOption('tag', null, Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'One or many tags that have assets you want to publish')
            ->setDescription('Publish any publishable assets from vendor packages');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->determineWhatShouldBePublished();

        foreach ($this->tags ?: [null] as $tag) {
            $this->publishTag($tag);
        }
    }

    /**
     * Determine the provider or tag(s) to publish.
     *
     * @return void
     */
    protected function determineWhatShouldBePublished()
    {
        if ($this->option('all')) {
            return;
        }

        [$this->provider, $this->tags] = [
            $this->option('provider'), (array) $this->option('tag'),
        ];

        if (!$this->provider && !$this->tags) {
            $this->promptForProviderOrTag();
        }
    }

    /**
     * Prompt for which provider or tag to publish.
     *
     * @return void
     */
    protected function promptForProviderOrTag()
    {
        $choice = $this->choice(
            "Which provider or tag's files would you like to publish?",
            $choices = $this->publishableChoices()
        );

        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    protected function publishableChoices()
    {
        return array_merge(
            ['<comment>Publish files from all providers and tags listed below</comment>'],
            preg_filter('/^/', '<fg=cyan>Provider:</> ', Arr::sort(ServiceProvider::publishableProviders())),
            preg_filter('/^/', '<fg=cyan>Tag:</> ', Arr::sort(ServiceProvider::publishableGroups()))
        );
    }

    /**
     * Parse the answer that was given via the prompt.
     *
     * @param  string  $choice
     * @return void
     */
    protected function parseChoice($choice)
    {
        [$type, $value] = explode(': ', strip_tags($choice));

        if ($type === 'Provider') {
            $this->provider = $value;
        } elseif ($type === 'Tag') {
            $this->tags = [$value];
        }
    }

    /**
     * Publishes the assets for a tag.
     *
     * @param  string  $tag
     * @return mixed
     */
    protected function publishTag($tag)
    {
        $published = false;

        $pathsToPublish = $this->pathsToPublish($tag);
        if ($publishing = count($pathsToPublish) > 0) {
            $this->components->info(sprintf(
                'Publishing %sassets',
                $tag ? "[$tag] " : '',
            ));
        }

        foreach ($pathsToPublish as $from => $to) {
            $this->publishItem($from, $to);
        }

        if ($publishing === false) {
            $this->info('No publishable resources for tag [' . $tag . '].');
        } else {
            $this->app->event->trigger(new AssetsTagPublished($tag, $pathsToPublish));
            $this->newLine();
        }
    }

    /**
     * Get all of the paths to publish.
     *
     * @param  string  $tag
     * @return array
     */
    protected function pathsToPublish($tag)
    {
        return ServiceProvider::pathsToPublish(
            $this->provider,
            $tag
        );
    }

    /**
     * Publish the given item from and to the given location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishItem($from, $to)
    {
        if (is_file($from)) {
            return $this->publishFile($from, $to);
        } elseif (is_dir($from)) {
            return $this->publishDirectory($from, $to);
        }

        $this->error("Can't locate path: <{$from}>");
    }

    /**
     * Publish the file to the given path.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishFile($from, $to)
    {
        if ((!$this->option('existing') && (!file_exists($to) || $this->option('force')))
            || ($this->option('existing') && file_exists($to))
        ) {
            $this->createParentDirectory(dirname($to));

            copy($from, $to);

            $this->status($from, $to, 'file');
        } else {
            if ($this->option('existing')) {
                $this->components->twoColumnDetail(sprintf(
                    'File [%s] does not exist',
                    str_replace(base_path() . '/', '', $to),
                ), '<fg=yellow;options=bold>SKIPPED</>');
            } else {
                $this->components->twoColumnDetail(sprintf(
                    'File [%s] already exists',
                    str_replace(base_path() . '/', '', realpath($to)),
                ), '<fg=yellow;options=bold>SKIPPED</>');
            }
        }
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function publishDirectory($from, $to)
    {
        $visibility = PortableVisibilityConverter::fromArray([], Visibility::PUBLIC);

        $this->moveManagedFiles(new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to' => new Flysystem(new LocalAdapter($to, $visibility)),
        ]));

        $this->status($from, $to, 'directory');
    }

    /**
     * Move all the files in the given MountManager.
     *
     * @param  \League\Flysystem\MountManager  $manager
     * @return void
     */
    protected function moveManagedFiles($manager)
    {
        foreach ($manager->listContents('from://', true) as $file) {
            $path = array_reverse(explode('from://', $file['path'], 2))[0];

            if (
                $file['type'] === 'file'
                && (
                    (!$this->option('existing') && (!$manager->fileExists('to://' . $path) || $this->option('force')))
                    || ($this->option('existing') && $manager->fileExists('to://' . $path))
                )
            ) {
                $manager->write('to://' . $path, $manager->read($file['path']));
            }
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param  string  $directory
     * @return void
     */
    protected function createParentDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     * @return void
     */
    protected function status($from, $to, $type)
    {
        $from = str_replace(base_path() . '/', '', realpath($from));

        $to = str_replace(base_path() . '/', '', realpath($to));
        $this->components->task(sprintf(
            'Copying (%s) [%s] to [%s]',
            $type,
            $from,
            $to,
        ));
    }
}
