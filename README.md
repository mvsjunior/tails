# Tails


| [![PHP](https://img.shields.io/badge/language-php-blue.svg)](https://choosealicense.com/licenses/mit/)         | [![MySQL](https://img.shields.io/badge/database-mysql-blue.svg)](https://choosealicense.com/licenses/mit/) |
|----------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------|


## O que é
Tails é uma implementação do repository pattern via ORM.

## Estrutura do diretório
```
 tails
  ├───Config
  │   └───Connections
  └───Model
  └───Repository
```

## Tutorial 

Exemplo de implementação no seguinte cenário.

```
...............................................
|                 persons                     |
...............................................
| id | name      | lastname    | birthdate    |
|----|-----------|-------------|--------------|
| 1  | Silvester | Estah Alone | 1971-07-25   |
'''''''''''''''''''''''''''''''''''''''''''''''
```

1 - Insira os dados de acesso ao banco de dados dentro do arquivo "tails\Config\Connections\default.ini"

Exemplo:
~~~~ini
dbHostname = "localhost"
dbName     = "teste"
dbUser     = "root"
dbPass     = ""
dbType     = "mysql"
dbPort     = 3306
~~~~

2 - Crie o arquivo Person.php em "tails\Model", que irá conter a classe que será o modelo baseado na tabela "persons"

~~~PHP
<?php

namespace VolgPhp\Tails\Model;

class Person {

    public $id;
    public $name;
    public $lastname;
    public $birthdate;
}
~~~

3 - Crie o arquivo PersonRepository.php, no diretório "tails\Repository". Esse arquivo irá conter a classe PersonRepository, que herda atributos e métodos de "RepositoryBase"
~~~PHP
<?php

namespace VolgPhp\Tails\Repository;

use VolgPhp\Tails\Repository\RepositoryBase;
use VolgPhp\Tails\Model\Person;

final class PersonRepository extends RepositoryBase {

    protected $TABLE      = "persons"; // Nome da tabela
    protected $modelClass = Person::class; // Classe do modelo
    protected $fillable   = ["name", "lastname", "birthdate"]; // nome das colunas que podem ser alteradas
}
~~~


4 - Executando as consultas no banco via repository

**Salvando um novo registro**
~~~PHP
<?php

include_once __DIR__ . "/vendor/autoload.php";

use VolgPhp\Tails\Repository\PersonRepository;
use VolgPhp\Tails\Model\Person;


$personRepo = new PersonRepository;

$person01 = new Person;

$person01->name      = "Kiano";
$person01->lastname  = "Orrivis";
$person01->birthdate = "1990-01-01";

// Salvando o registro
$personRepo->save( [ $person01 ] );

~~~



**Obtendo todos os registros**
~~~PHP
<?php

include_once __DIR__ . "/vendor/autoload.php";

use VolgPhp\Tails\Repository\PersonRepository;
use VolgPhp\Tails\Model\Person;


$personRepo = new PersonRepository;

// Obtendo todos os registros
$persons = $personRepo->getAll();

var_dump($persons);
~~~



**Exibindo um único registro**
~~~PHP
<?php

include_once __DIR__ . "/vendor/autoload.php";

use VolgPhp\Tails\Repository\PersonRepository;
use VolgPhp\Tails\Model\Person;


$personRepo = new PersonRepository;

// Obtendo um único registro por id
$id = 3;
$person = $personRepo->find($id);

var_dump($person);
~~~



**Editando registro existente**
~~~PHP
<?php

include_once __DIR__ . "/vendor/autoload.php";

use VolgPhp\Tails\Repository\PersonRepository;
use VolgPhp\Tails\Model\Person;


$personRepo = new PersonRepository;

// Obtendo um registro por id
$id = 3;
$person = $personRepo->find($id);

// Editando um registro e salvando as alterações
$person->name = "John";
$person->lastname = "Rambo";
$person->birthdate = "1978-01-01";

// Salvando as alterações no banco
$personRepo->update([ $person ]);
~~~