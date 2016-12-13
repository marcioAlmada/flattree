<h1 align="center">
    <img src="https://dl.dropboxusercontent.com/u/49549530/flattree/flaTTree.png" title="Flat Tree">
</h1>

## Set Up

```bash
composer require marc/flattree:dev-master
```

## Usage

### Recursive Adjacent List

Consider you have the following adjacent list as data representing some recursive tree:

|employee_id|parent_id|job_title|first_name|
|---|---|---|---|
|1|NULL|'Managing Director'|'Bill'|
|2|1|'Customer Services'|'Angela'|
|3|1|'Development Manager'|'Ben'|
|4|2|'Assistant 1'|'Henry'|
|5|2|'Assistant 2'|'Nicola'|
|6|3|'Snr Developer'|'Kerry'|
|7|3|'Assistant'|'James'|
|8|6|'Jrn Developer'|'Tim'|

To build a tree based on the given dataset, where `parent_id=employee_id`:

```php
use marc\flatrree\{unfold_recursive, debug};

$tree = unfold_recursive($associative_data, 'parent_id', 'employee_id');

echo debug($tree, "{job_title}: {first_name}");
```
Outputs:
```
├─ <null>: <null>
│  ├─ Managing Director: Bill
│  │  ├─ Customer Services: Angela
│  │  │  └─ Assistant 1: Henry
│  │  │  └─ Assistant 2: Nicola
│  │  ├─ Development Manager: Ben
│  │  │  ├─ Snr Developer: Kerry
│  │  │  │  └─ Jrn Developer: Tim
│  │  │  └─ Assistant: James

```

### Non Recursive Adjacent List

|id|class|animal|breed|size|
|---|---|---|---|---|
|1|'mammal'|'dog'|'Dalmatian'|'big'|
|2|'mammal'|'dog'|'Bulldog'|'small'|
|3|'mammal'|'dog'|'Lhasa Apso'|'small'|
|4|'mammal'|'cat'|'Persian'|'small'|

Build a tree grouping by `class` and `animal`:

```php
use marc\flattree\{unfold, debug};

$tree = unfold($data, ['class', 'animal']);

echo debug($tree, ['{:level}', '{:level}', '{breed}']);
```
Outputs:
```
├─ mammal
│  ├─ dog
│  │  └─ Dalmatian
│  │  └─ Bulldog
│  │  └─ Lhasa Apso
│  ├─ cat
│  │  └─ Persian

```

One more level of grouping, now by `class` and `animal` and `size`:

```php
use marc\flattree\{unfold, debug};

$tree = unfold($associative_data, ['class', 'animal', 'size']);

echo debug($tree, ['{:level}', '{:level}', '{:level}', '{breed}']);
```
Outputs:
```
├─ mammal
│  ├─ dog
│  │  ├─ big
│  │  │  └─ Dalmatian
│  │  ├─ small
│  │  │  └─ Bulldog
│  │  │  └─ Lhasa Apso
│  ├─ cat
│  │  ├─ small
│  │  │  └─ Persian

```

> Reference http://www.ibase.ru/files/articles/programming/dbmstrees/sqltrees.html

## Copyright

Copyright (c) 2016-* Márcio Almada. Distributed under the terms of an MIT-style license.
See LICENSE for details.
