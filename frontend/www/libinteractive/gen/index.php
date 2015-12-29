<?php
    require_once('../../../server/bootstrap.php');

if ($_POST) {
    if (!in_array($_POST['language'], array('c', 'cpp', 'java'))) {
        $smarty->assign('error', $smarty->getConfigVars('parameterInvalid'));
        $smarty->assign('error_field', 'language');
    } elseif (!in_array($_POST['os'], array('unix', 'windows'))) {
        $smarty->assign('error', $smarty->getConfigVars('parameterInvalid'));
        $smarty->assign('error_field', 'os');
    } elseif (!preg_match('/^[a-z_][a-z0-9_]{0,31}$/i', $_POST['name'])) {
        $smarty->assign('error', $smarty->getConfigVars('parameterInvalid'));
        $smarty->assign('error_field', 'name');
    } elseif (empty($_POST['idl'])) {
        $smarty->assign('error', $smarty->getConfigVars('parameterInvalid'));
        $smarty->assign('error_field', 'idl');
    } else {
        $dirname = FileHandler::TempDir(sys_get_temp_dir(), 'libinteractive');
        try {
            file_put_contents("{$dirname}/{$_POST['name']}.idl", $_POST['idl']);
            $args = array('/usr/bin/java', '-jar', '/opt/omegaup/bin/libinteractive.jar',
                'generate', "{$_POST['name']}.idl", $_POST['language'], $_POST['language'],
                '--makefile', "--{$_POST['os']}");
            $descriptorspec = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            );
            $cmd = join(' ', array_map('escapeshellarg', $args));
            $proc = proc_open(
                $cmd,
                $descriptorspec,
                $pipes,
                $dirname,
                array('LANG' => 'en_US.UTF-8')
            );
            if (!is_resource($proc)) {
                $smarty->assign('error', error_get_last());
            } else {
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                $err = stream_get_contents($pipes[2]);
                $retval = proc_close($proc);

                if ($retval != 0) {
                    $smarty->assign('error', $output . $err);
                } else {
                    $zip = new ZipArchive();
                    $zip->open(
                        "{$dirname}/interactive.zip",
                        ZipArchive::CREATE | ZipArchive::OVERWRITE
                    );

                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($dirname),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );

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
                    header("Content-Disposition: attachment; filename={$_POST['name']}.zip");
                    readfile("{$dirname}/interactive.zip");
                    FileHandler::DeleteDirRecursive($dirname);
                    die();
                }
            }
        } catch (Exception $e) {
            $smarty->assign('error', $e);
        } finally {
            FileHandler::DeleteDirRecursive($dirname);
        }
    }
}
    $smarty->display('../../../templates/libinteractive.gen.tpl');
