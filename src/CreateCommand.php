<?

  namespace Funivan\Skeleton;


  use Symfony\Component\Console\Command\Command;
  use Symfony\Component\Console\Helper\QuestionHelper;
  use Symfony\Component\Console\Helper\Table;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\ConfirmationQuestion;
  use Symfony\Component\Finder\Finder;
  use Symfony\Component\Process\Process;

  /**
   *
   * Create project from skeleton
   *
   * @package Funivan\Skeleton
   */
  class CreateCommand extends Command {

    protected function configure() {
      $this->setName("create");
      $this->setDescription("Create files");

      $this->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Repository name');
      $this->addOption('package', 'p', InputOption::VALUE_REQUIRED, 'Package name');

      $this->addOption('description', 'd', InputOption::VALUE_REQUIRED, 'Package description', '');

      $this->addOption('path', null, InputOption::VALUE_REQUIRED, "Repository path. Used on GitHub and Packagist links. It will be automatically generated from repository and package options");

      $this->addOption('author_github_name', null, InputOption::VALUE_REQUIRED, 'Author github nickname', $this->getDefaultValueFromGit('user.github-name'));
      $this->addOption('author_name', null, InputOption::VALUE_REQUIRED, 'Author name', $this->getDefaultValueFromGit('user.name'));
      $this->addOption('author_email', null, InputOption::VALUE_REQUIRED, 'Author email address', $this->getDefaultValueFromGit('user.email'));
      $this->addOption('author_website', null, InputOption::VALUE_REQUIRED, 'Author website url', $this->getDefaultValueFromGit('user.website'));

      $this->addOption('year', null, InputOption::VALUE_REQUIRED, 'Year when package was created', date('Y'));
      $this->addOption('destination', null, InputOption::VALUE_REQUIRED, 'Destination path', getcwd());


      parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

      $repository = $input->getOption('repository');
      if (empty($repository) or !preg_match('!^[a-zA-Z]+[a-zA-Z_0-9]$!', $repository)) {
        $output->writeLn('<error>Invalid option --repository. Allowed characters a-zA-Z0-9_ </error>');
        return;
      }


      $package = $input->getOption('package');
      if (empty($package)) {
        $output->writeLn('<error>Invalid option --package</error>');
        return;
      }


      $path = $input->getOption('path');

      if (!empty($path) and !is_string($path)) {
        $output->writeLn('<error>Invalid option --path</error>');
        return;
      }

      if (empty($path)) {
        $path = $repository . '/' . $package;
        $path = preg_replace_callback('!([^/])([A-Z]+)!', function ($match) {
          return $match[1] . '-' . strtolower($match[2]);
        }, $path);

        $path = preg_replace('!-{2,}!', '-', $path);
        $path = strtolower($path);
      }

      $destination = $input->getOption('destination');

      $configuration = array(
        'repository' => $repository,
        'package' => $package,
        'path' => $path,
        'description' => $input->getOption('description'),
        'author_github_name' => $input->getOption('author_github_name'),
        'author_name' => $input->getOption('author_name'),
        'author_email' => $input->getOption('author_email'),
        'author_website' => $input->getOption('author_website'),
        'year' => $input->getOption('year'),
        'destination' => $destination,
      );

      /** @var Table $table */
      $table = new Table($output);
      $table->setHeaders(array('Key', 'Value'));
      foreach ($configuration as $key => $value) {
        $table->addRow(array($key, $value));
      }
      $table->render();


      $helper = new QuestionHelper();


      if (!$input->getOption('no-interaction')) {

        $question = new ConfirmationQuestion('Continue with this configuration (y/n) : ', false);
        if (!$helper->ask($input, $output, $question)) {
          return;
        }
      }


      if (!is_dir($destination)) {
        if (!$input->getOption('no-interaction')) {
          $question = new ConfirmationQuestion(sprintf('Directory %s does not exist. Create (y/n) : ', $destination), false);
          if (!$helper->ask($input, $output, $question)) {
            return;
          }
        }

        if (!mkdir($destination)) {
          $output->writeLn(sprintf('<error>Cant create directory project: %s</error>', $destination));
          return;
        }
      }


      $templatesDir = __DIR__ . '/../templates';

      \Twig_Autoloader::register();
      $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($templatesDir));
      foreach ($configuration as $key => $value) {
        $twig->addGlobal($key, $value);
      }

      $finder = new Finder();
      $finder->files()->in($templatesDir)->ignoreDotFiles(false);

      foreach ($finder as $file) {

        $destinationDir = $destination . '/' . $file->getRelativePath();
        if ($file->getRelativePathname() != $file->getRelativePath() and !is_dir($destinationDir)) {
          if (!mkdir($destinationDir)) {
            $output->writeLn(sprintf('<error>Cant create directory : %s</error>', $destinationDir));
            return;
          }
        }


        $fileName = $file->getRelativePathname();
        $fileName = preg_replace('!\.twig$!', '', $fileName);
        $destinationFile = $destination . '/' . $fileName;


        $template = $twig->loadTemplate($file->getRelativePathname());
        $fileData = $template->render(array());


        if (!file_put_contents($destinationFile, $fileData)) {
          $output->writeLn(sprintf('<error>Cant create file: %s</error>', $destinationFile));
          return;
        }

        $output->writeln(sprintf("<info>File created: %s</info>", $destinationFile));
      }

    }

    /**
     * @param string $key
     * @return string
     */
    private function getDefaultValueFromGit($key) {

      if (!is_string($key) or empty($key)) {
        throw new \InvalidArgumentException("Invalid key. Expect not empty string");
      }

      $process = new Process('git config --global --get ' . $key);
      $process->run();

      if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
      }

      return trim($process->getOutput());
    }

  }
  