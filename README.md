# Deployer Sync Recipe

Simple  [Deployer](https://github.com/deployphp/deployer) recipe for synchronize directories and databases between servers

Define sync-specified settings in your `servers.yml` file

```yml
staging:
  host: staging.domain.com
  user: www
  identity_file: ~
  stage: staging
  deploy_path: /home/www/
# sync-specified settings starts
  app:
    db: # used for sync:db task
      name: dbname
      user: dbusername
      password: dbpassword
      host: localhost
  sync: # used for sync:dirs task
    source-server: production
    source-path: /home/www
    dest-path: ./shared/
    dirs:
      - upload
      - important
    excludes:
      - 'cache'
      - 'tmp'
# sync-specified settings ends

production:
  host: domain.com
    user: www
    password: pass
    stage: production
    deploy_path: /home/www/
# sync-specified settings starts
    app:
      db: # used for sync:db task
        name: dbnameprod
        user: dbuserprod
        password: dbprodpassword
        host: localhost
# sync-specified settings ends
```

Or you can define sync settings with `set()` and/or `server()` functions

You can set or override `source-server` and `dest-path` via  options keys:

```shell
$ dep sync develop  --source-server production --dest-path ./ 
$ dep sync:db develop  --source-server production # only database sync
$ dep sync:dirs develop  --source-server production --dest-path ./  # only directories sync
```
