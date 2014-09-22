# Lucene Query Builder

## Installation
### Composer

Add to composer.json:-

```` json
{
    "require": {
        "fabieno/lucenequerybuilder": "dev-master"
    }
}
````

## Example usage

```` php
<?php
require_once 'vendor/autoload.php';

// initialise query instance
$query = new LuceneQueryBuilder\Query();

// URL matcher
$query->mayContain('Dr. Dre', 'title')
      ->mustContain('Next Episode', 'body')
      ->mustNotContain('Britney Spears', 'body')
      ->mayHaveInProximity('Snoop Dogg', 'body', 10)
      ->mustHaveInProximity('Eminem', 'body', 7);

// compile query - returns title: "Dr. Dre" AND body: (+"Next Episode" -"Britney Spears" "Snoop Dogg"~10 +"Eminem"~7)
$query->compile();
