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
$ santizen config lang="php" framework="santorini"
$ santizen config php.namespace="com.sansting.openapi"
$ santizen config santorini.prefix_url="sansting/v1"
```

- fetch

```
$ santizen fetch board_topic board_comment
```

- help

```
$ santizen help
santizen variables:
  - OUTPUT.DIR: ../target
  - DB.HOST: localhost
  - DB.NAME: sansting
  - DB.USERNAME: root
  - DB.PASSWORD: autoset
  - LANG: php
  - FRAMEWORK: santorini
  - DB.TYPE: mysql
  - PHP.NAMESPACE: com.sansting.openapi
  - SANTORINI.PREFIX_URL: /openapi/v1

database schemes:
  - board_topic
  - board_comment
```

- generate

```
$ santizen generate
```