# yii-elasticseach-wrapper
Wrapper class component for yii1

Installation notes:

Put following array into main.php or local.php into components section:
```
'elasticsearch' => [
    'class' => 'application.components.Elasticsearch', //path to es wrapper class 
    'host' => 'x.x.x.x', //es host
    'port' => xxxx, //es port
    'prefix' => 'xxx_' //prefix to index, this is useful if you have one es server and multiple test hosts within prod server
]
```

Dependencies:

This library is dependent to [yii extension`s curl wrapper library](https://github.com/hackerone/curl/) 

Usage:

```
Yii::app()->elasticsearch->ifIndexExist($index); //check whether index exist
Yii::app()->elasticsearch->deleteIndex($index); //delete index
Yii::app()->elasticsearch->createIndex($index); //create index
Yii::app()->elasticsearch->createMapping($index, $type, $criteria); //create mapping
Yii::app()->elasticsearch->insert($index, $type, $document); //add new document
Yii::app()->elasticsearch->deleteByQuery($index, $type, $query); //delete document by criteria
Yii::app()->elasticsearch->search($index, $type, $query); //search by criteria
```

P.S:

ES v6.2

Draft version