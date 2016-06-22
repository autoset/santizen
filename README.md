# santizen
Santorini Code Generator

```
$ santizen action arguments
```

## Actions

- clean

- config

```
$ santizen config output.dir=../target
$ santizen config db.type="mysql" db.host="localhost" db.username="root" db.password="autoset" db.name="sansting"
$ santizen config lang="php5" framework="santorini"
$ santizen config php.namespace="com.sansting.openapi"
```

- fetch

```
santizen fetch board_topic board_comment
```

- help

```
santizen variables:
  - DB.HOST: localhost
  - DB.NAME: sansting
  - DB.USERNAME: root
  - DB.PASSWORD: autoset
  - DB.TYPE: mysql
  - OUTPUT.DIR: ../target
  - LANG: php
  - FRAMEWORK: santorini

database schemes:
  - board_topic
  - board_comment
```

- generate
