# Buxus/Bql

Buxus/Bql je interpreter dopytovacieho jazyka BQL používaného v CMS Buxus. Zjednodušuje písanie databázových dopytov pre výber stránok a ich vlastností.

Je implementovaný v jazyku PHP vo verzii 5.5 a vystupuje ako modul pre CMS Buxus pod názvom „buxus/bql“ prostredníctvom správcu závislostí Composer.  
Zo zadaných vstupných dopytov v jazyku BQL vytvára SQL dopyty, ktoré zodpovedajú syntaxe systému správy relačných databáz MySQL 5.5.

Syntax jazyka BQL je podobná jazyku SQL. Namiesto názvov tabuliek sa v dopytoch používajú označenia typov stránok (page_type_tag) a namiesto stĺpcov označenia vlastností (property_tags):

```sql
SELECT [property_tags] FROM [page_type_tag] WHERE [podmienky]   
```

Podporovanými príkazmi sú:
  - SELECT
  - FROM
  - WHERE
  - GROUP BY
  - HAVING
  - ORDER BY
  - LIMIT
  - JOIN

Podporované sú tiež všetky operátory a agregačné funkcie z SQL. BQL tiež umožňuje vytvárať vnorené dopyty a vyberať vlastnosti z viacero typov stránok prostredníctvom príkazu JOIN.

### Závislosti

Pre svoje fungovanie BQL interpreter využíva tieto balíky:

* [PHPSQLParser] - Parser SQL dopytov do poľa
* [FluentPDO] - Fluent interface pre vyskladávanie SQL dopytov

### Inštalácia

Nakoľko je interpreter vytvorený ako modul redakčného systému Buxus, predpokladom na jeho inštaláciu a používanie je funkčná inštalácia CMS Buxus a nainštalovaný správca závislostí [Composer](https://getcomposer.org), ktorý interpreter využíva. 

Do časti „require“ už existujúceho súboru composer.json inštalácie CMS Buxus je následne potrebné pridať závislosť na balíku „buxus/bql“:

```json
"require": {  
    "buxus/bql": "dev-master"
} 
```

Následne stačí spustiť composer príkaz na inštaláciu a aktualizáciu závislostí: 

```sh
$ php composer.phar update 
```

Tento príkaz okrem inštalácie samotného modulu „buxus/bql“ nainštaluje aj všetky jeho závislosti. Po úspešnom vykonaní príkazu by mal byť modul „buxus/bql“ nainštalovaný v adresári vendor/buxus/bql. 

### Nastavenie spúšťania cez konzolu

V CMS Buxus je možné nastaviť PHP príkaz, ktorým bude možné BQL interpreter spúšťať priamo z konzoly. 
Do súboru buxus_commands.php v adresári buxus/config je potrebné pridať riadok nasledujúci riadok: 

```sh
\Buxus\Cli\BuxusCli::getInstance() ->addCommand(\App\Command\BqlCommand::class);
```

Následne do adresára buxus/models/App/Command prekopírovať súbor BqlCommand.php z koreňového adresára Buxus/Bql.

### Používanie prostredníctvom konzoly
Po úspešnej inštalácií a nastavení môžeme BQL interpreter spúšťať priamo z konzoly, a to príkazom: 
```sh
$ php command app:bql “<dopyt>“ 
```
Slovo <dopyt> nahradíme BQL dopytom, ktorý chceme prekonvertovať do SQL. Výstupom je výpis obsahu poľa obsahujúceho rozparsovaný zadaný BQL dopyt a výpis dopytu v jazyku SQL. 

Príklad použitia:
```sh
$ php command app:bql “SELECT * FROM eshop_product“ 
```

### Používanie v PHP skriptoch
#### Dopyt v klasickom zápise
Pre možnosť používania BQL interpretera v PHP skripte je potrebné na jeho začiatok uviesť, že používa triedu Bql balíčka Buxus\BQL príkazom:
```php
use Buxus\Bql\Bql;
```
Následne vytvoríme v kóde inštanciu triedy Bql(): 
```php
$bql = new Bql();
```
Teraz môžeme volať metódu getSQL($query) triedy Bql(), ktorá zo zadaného BQL dopytu vracia dopyt v SQL zápise.
```php
$bql = new Bql(); 
$SQLquery=$bql->getSQL("SELECT * FROM eshop_product");   
```
####Dopyt vyskladaný pomocou Fluent rozhrania
Fluent rozhranie BQL interpretera poskytuje trieda Buxus\Bql\QueryBuilder. V PHP skripte, v ktorom chceme toto rozhranie používať preto pridáme riadok:
```php
use Buxus\Bql\QueryBuilder;
```
a vytvoríme inštanciu triedy QueryBuilder():
```php
$qb = new QueryBuilder();
```
Prostredníctvom premennej $qb teraz môžeme pristupovať k metódam na vyskladávanie dopytov.  

Jednotlivé metódy poskytuje balíček „FluentPDO“. BQL interpreter z neho podporuje tieto metódy: 
- from($table)
- select($column)
- where($condition)
- groupBy($column)
- having($column)
- innerJoin($statement)
- leftJoin($statement)
- limit($limit)
- offset($offest)
- orderBy($column) 

Okrem nich BQL interpreter poskytuje aj tieto vlastné metódy: 
- getBQL() – vráti vyskladaný BQL dopyt v klasickom zápise 
- getSQL() – vráti výsledný SQL zápis z vykladaného BQL dopytu 

Príklad použitia:

```php
$qb = new QueryBuilder();
$qb->from('eshop_product')
   ->select('page_name, eshop_eur_price_without_vat')
   ->getSQL();
```

### Testy
Pre overenie funkčnosti BQL interpretera sme vytvorili niekoľko testov, ktoré pokrývajú všetky príkazy podporované navrhnutým BQL jazykom. 
Testy sa nachádzajú v adresári src/tests a spúšťajú sa prostredníctvom skriptu run.php, ktorý porovnáva očakávané výstupy s aktuálnymi. Príkaz je možné spustiť priamo z konzoly príkazom:
```php
$ php run.php
```
pričom je možné použíť prepínače: 
- -v : v prípade neúspešnosti testu zobrazí očakávaný a aktuálny výstup  
- -d : zobrazí očakávaný a aktuálny výstup pre každý test 

Pre každý test je vypísaný čas, za ktorý sa test vykonal. Po spustení všetkých testov je vypísaný počet úspešných a neúspešných testov, ako aj celkový čas vykonávania testov a množstvo spotrebovanej pamäte. 

   [FluentPDO]: <https://github.com/fpdo/fluentpdo>
   [PHPSQLParser]: <https://github.com/greenlion/PHP-SQL-Parser/tree/master/src/PHPSQLParser>