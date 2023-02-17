<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 17/08/2018
 * Time: 15:17
 */

namespace Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class LayoutToEntity extends SymfonyCommand
{
    public function configure()
    {
        $this -> setName('convert')
            -> setDescription('Convert a JSON representation of a FileMaker layout to a Doctrine entity.')
            -> addArgument('file', InputArgument::REQUIRED, 'Path to the json file exported from FileMaker.')
            -> addArgument('destination', InputArgument::REQUIRED, 'Location to save the entity. If you also generate a repo, then they will be put in appropriate sub folders of the location given.')
            -> addArgument('entity', InputArgument::REQUIRED, 'The name of the entity to generate.')
            -> addOption('repo', 'r', InputOption::VALUE_OPTIONAL, 'Should a repository also be generated.', false)
            -> addOption('attributes', 'a', InputOption::VALUE_OPTIONAL, 'Use PHP 8 attributes rather than comment-based annotations.', false);
    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $this->loadFieldsFromFile($input->getArgument('file'), $output);
        $content = $this->generateHeader($input, $json->layout);
        $generator = $input->getOption('attributes') ? ComponentsAttributes::class : Components::class;

        foreach($json->fields as $field) {
            $id = isset($field->id) && $field->id;
            $content .= $generator::{$field->type}($field->field, $id);
        }
        $content .= $generator::footer();

        $this->writeToFile($input, $output, $content);
        $output->writeln('<info>Entity generated</info>');
    }


    /**
     * @param $file
     * @param OutputInterface $output
     *
     * @return object
     */
    private function loadFieldsFromFile($file, OutputInterface $output)
    {
        if(!file_exists($file)) {
            $output->writeln('<error>Unable to find file. Please check the path is correct</error>');
            exit();
        }

        return json_decode(file_get_contents($file), false);
    }

    private function generateHeader(InputInterface $input, $layout)
    {
        $entity = $input->getArgument('entity');
        $generator = $input->getOption('attributes') ? ComponentsAttributes::class : Components::class;

        return $generator::header($entity, $layout, $input->getOption('repo'));
    }


    private function writeToFile(InputInterface $input, OutputInterface $output, string $content)
    {
        $entity = $input->getArgument('entity');
        $path = $this->entityDestination($input);

        if(!is_writable($path)) {
            $output->writeln(sprintf('<error>Unable to write to destination %s</error>', $path));
            exit();
        }

        $file =  $path . $entity .'.php';
        if(file_exists($file)) {
            $helper = $this->getHelper('question');
            $output->writeln('<question>This entity already exists and will be overwritten.</question>');
            $question = new ConfirmationQuestion('Continue with this action? [y/N]', false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<info>Aborted write as file already exists</info>');
                exit();
            }
        }

        file_put_contents($file, $content);
        if($input->getOption('repo')) {
            $repo = $this->repoDestination($input);
            file_put_contents($repo, Components::repo($entity));
        }
    }


    private function entityDestination(InputInterface $input)
    {
        $base = $this->basePath($input);
        if($input->getOption('repo')) {
            $base .= 'Entity' . DIRECTORY_SEPARATOR;
        }

        return $base;
    }

    private function repoDestination(InputInterface $input)
    {
        return $this->basePath($input) . 'Repository' . DIRECTORY_SEPARATOR .
            $input->getArgument('entity') . 'Repository.php';
    }

    private function basePath(InputInterface $input)
    {
        $base = $input->getArgument('destination');
        if(DIRECTORY_SEPARATOR != substr($base, -1)) {
            $base .= DIRECTORY_SEPARATOR;
        }

        return $base;
    }
}
