<?php

namespace Deployer;

option(
    'source-server',
    null,
    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
    '[sync] Server to get data from.'
);
option(
    'dest-path',
    null,
    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
    '[sync] Path to save sync directories to.'
);


desc('Synchronize database between servers');
task(
    "sync:db",
    function () {

        $deployer = Deployer::get();

        $sourceServerName = input()->getOption('source-server');

        if ($sourceServerName === null) {
            $sourceServerName = get('sync.source-server');
        }

        $sourceServerConfig = $deployer->servers->get($sourceServerName)->getConfiguration();
        $sourceServerEnv = $deployer->environments->get($sourceServerName);



        $destServerName = get('server.name');
        $destServerConfig = $deployer->servers->get($destServerName)->getConfiguration();
        $destServerEnv = $deployer->environments->get($destServerName);



        $sourceServer = [
            'user' => $sourceServerConfig->getUser(),
            'host' => $sourceServerConfig->getHost(),
            'port' => $sourceServerConfig->getPort(),
            'app.db.user' => $sourceServerEnv->get('app.db.user')
        ];


        run(escapeshellcmd(" ssh -p {$sourceServerConfig->getPort()} 
            {$sourceServerConfig->getUser()}@{$sourceServerConfig->getHost()} 
            'mysqldump --default-character-set=utf8 -h {$sourceServerEnv->get('app.db.host')} 
            -u {$sourceServerEnv->get('app.db.user')} -p{$sourceServerEnv->get('app.db.password')} 
            {$sourceServerEnv->get('app.db.name')}  --skip-lock-tables --add-drop-table'")
            ." | ". escapeshellcmd(" mysql -h {$destServerEnv->get('app.db.host')}
             -u{$destServerEnv->get('app.db.user')}  -p{$destServerEnv->get('app.db.password')} 
            {$destServerEnv->get('app.db.name')}"), 0);
    }
);

desc('Synchronize shared directories between servers');
task(
    "sync:dirs",
    function () {
        cd("{{deploy_path}}");
        $deployer = Deployer::get();

        $sourceServerName = input()->getOption('source-server');

        if ($sourceServerName === null) {
            $sourceServerName = get('sync.source-server');
        }

        $sourceServerConfig = $deployer->servers->get($sourceServerName)->getConfiguration();
        $sourceServerEnv = $deployer->environments->get($sourceServerName);


        $destPath = input()->getOption('dest-path');

        if ($destPath == null) {
            $destPath = get('sync.dest-path');
        }


        $sourcePath = get('sync.source-path');

        foreach (get('sync.dirs') as $dir) {
            $excludeString = '';
            foreach (get('sync.excludes') as $exclude) {
                $excludeString .= " --exclude=$exclude ";
            }

            run(escapeshellcmd("rsync -e \"ssh -p {$sourceServerConfig->getPort()}\" -avz --delete 
                {$sourceServerConfig->getUser()}@{$sourceServerConfig->getHost()}:{$sourcePath}/$dir/ 
                $destPath/$dir $excludeString"), 0);
        }
    }
);

task(
    'sync',
    [
        'sync:dirs',
        'sync:db'
    ]
);