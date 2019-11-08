<?php
require_once('../../../server/bootstrap_smarty.php');

if (!empty($_POST)) {
    if (!in_array($_POST['language'], ['c', 'cpp', 'java'])) {
        $smarty->assign(
            'error',
            \OmegaUp\Translations::getInstance()->get(
                'parameterInvalid'
            )
        );
        $smarty->assign('error_field', 'language');
    } elseif (!in_array($_POST['os'], ['unix', 'windows'])) {
        $smarty->assign(
            'error',
            \OmegaUp\Translations::getInstance()->get(
                'parameterInvalid'
            )
        );
        $smarty->assign('error_field', 'os');
    } elseif (
        !preg_match(
            '/^[a-z_][a-z0-9_]{0,31}$/i',
            strval(
                $_POST['name']
            )
        )
    ) {
        $smarty->assign(
            'error',
            \OmegaUp\Translations::getInstance()->get(
                'parameterInvalid'
            )
        );
        $smarty->assign('error_field', 'name');
    } elseif (empty($_POST['idl'])) {
        $smarty->assign(
            'error',
            \OmegaUp\Translations::getInstance()->get(
                'parameterInvalid'
            )
        );
        $smarty->assign('error_field', 'idl');
    } else {
        $dirname = \OmegaUp\FileHandler::TempDir(
            sys_get_temp_dir(),
            'libinteractive'
        );
        try {
            file_put_contents("{$dirname}/{$_POST['name']}.idl", $_POST['idl']);
            $args = ['/usr/bin/java', '-jar', '/usr/share/java/libinteractive.jar',
                'generate', "{$_POST['name']}.idl", $_POST['language'], $_POST['language'],
                '--makefile', "--{$_POST['os']}"];
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $cmd = join(' ', array_map('escapeshellarg', $args));
            $proc = proc_open(
                $cmd,
                $descriptorspec,
                $pipes,
                $dirname,
                ['LANG' => 'en_US.UTF-8']
            );
            if (!is_resource($proc)) {
                $smarty->assign('error', error_get_last());
            } else {
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $err = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                $retval = proc_close($proc);

                if ($retval != 0) {
                    $smarty->assign('error', $output . $err);
                } else {
                    $zip = new \ZipArchive();
                    $zip->open(
                        "{$dirname}/interactive.zip",
                        ZipArchive::CREATE | ZipArchive::OVERWRITE
                    );

                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($dirname),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    /** @var \SplFileInfo $file */
                    foreach ($files as $name => $file) {
                        if ($file->isDir()) {
                            continue;
                        }
                        if ($file->getFilename() == 'interactive.zip') {
                            continue;
                        }

                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($dirname) + 1);

                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }

                    $zip->close();

                    header('Content-Type: application/zip');
                    header(
                        "Content-Disposition: attachment; filename={$_POST['name']}.zip"
                    );
                    readfile("{$dirname}/interactive.zip");
                    \OmegaUp\FileHandler::deleteDirRecursively($dirname);
                    die();
                }
            }
        } catch (Exception $e) {
            $smarty->assign('error', $e);
        } finally {
            \OmegaUp\FileHandler::deleteDirRecursively($dirname);
        }
    }
}

$smarty->display('../../../templates/libinteractive.gen.tpl');
