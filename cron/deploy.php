<?php
///usr/local/bin/php /home/fhsjewrv/mygram.in.ua/cron/email.php >/dev/null 2>&1
//cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php cron/deploy.php -f -v >/dev/null 2>&1
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('GIT_COMMAND', "/usr/bin/git");
define('JAVA_PATH', '/home/bin/');
define('PHP_COMMAND', '/usr/bin/php');
$current_dir = dirname(__FILE__);

define('REMOTE_REPO', 'git@github.com:igorbunov/mygram.git');
define('LOCAL_REPO', realpath($current_dir . '/../'));
define('BRANCH', 'master');
define('DEPLOY_DIR', '/home/fhsjewrv/mygram.in.ua');
define('NEED_DEPLOY_FILE', DEPLOY_DIR . '/gh34574trg5hrtg/deploy');
define('DEBUG', option_exists('v'));

putenv("PATH=" . JAVA_PATH . PATH_SEPARATOR . getenv('PATH'));

function system_message($message) {
    if (DEBUG) {
        echo "**** $message ****", PHP_EOL;
    }
}

function run_command($command) {
    $output = '';
    if (DEBUG) {
        echo "Running: $command", PHP_EOL;
        passthru($command . " 2>&1", $return);
    } else {
        exec($command . " 2>&1", $output, $return);
    }
    if ($return) {
        echo "Running:\n*** $command ***\n failed with exit status $return";
        if ($output) {
            echo ", output was:\n" . implode(PHP_EOL, $output);
        }
        echo PHP_EOL;

        exit($return);
    }
}

function update_repo() {
    system_message('Updating repo');
    $old_dir = getcwd();
    chdir(LOCAL_REPO);
    run_command(sprintf('%s fetch origin', GIT_COMMAND));

    $branch = get_required_branch();
    run_command(sprintf('%s checkout %s', GIT_COMMAND, $branch));
    run_command(sprintf('%s reset --hard origin/%s', GIT_COMMAND, $branch));
    chdir($old_dir);
}

function get_required_branch() {
    if (BRANCH !== 'stage') {
        return BRANCH;
    }

    $old_dir = getcwd();
    chdir(LOCAL_REPO);

    exec(sprintf("%s remote prune origin",  GIT_COMMAND));
    exec(sprintf("%s branch -r | grep -oE '(release|hotfix)/.*' | sort -rV -t '/' -k 2 | head -n 1",  GIT_COMMAND), $output);

    chdir($old_dir);
    return !empty($output) ? implode("", $output) : 'master';
}

function deploy() {
    system_message('Deploying fresh copy');

    $local_repo = rtrim(LOCAL_REPO, '/') . '/';
    $deploy_dir = rtrim(DEPLOY_DIR, '/') . '/';
    // for dry run add -n
    // FF - means look into .rsync-filter in each dir and do not touch it itself
    run_command(sprintf("rsync -av --delete --delete-excluded --filter='. $local_repo/.rsync-filter' $local_repo $deploy_dir"));
}

function composer()
{
    //composer  install
    $deploy_dir = rtrim(DEPLOY_DIR, '/') . '/';
    run_command(sprintf("cd $deploy_dir && composer install"));
}

function run_migrations() {
    $deploy_dir = rtrim(DEPLOY_DIR, '/') . '/';
    run_command(sprintf("cd $deploy_dir && /usr/local/bin/php artisan migrate"));
}

function option_exists($option) {
    $options = getopt("f::v::");
    return isset($options[$option]);
}

function is_already_running() {
    $command = sprintf('ps ax | grep %s | grep -v grep | grep -v vim | grep -v "/bin/sh " | wc -l', __FILE__);
    $result = trim(shell_exec($command));
    return $result > 1;
}

function need_deploy() {
    return file_exists(NEED_DEPLOY_FILE);
}

if (is_already_running()) {
    system_message('Already running, exiting.');
    exit();
}

update_repo();
run_migrations();

system_message('done');