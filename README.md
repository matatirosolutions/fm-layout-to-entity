# FileMaker layout to entity generator #

This command will convert a json object to a Doctrine Entity. It's been written to support the work which we do linking Symfony to FileMaker but could be used to convert any correctly structured json to a Doctrine entity (though note it only supports the FileMaker field types which makes it **very** incomplete.)

## Installation ##

```bash
    mkdir layout-to-entity && cd layout-to-entity
    git clone git@github.com:matatirosolutions/fm-layout-to-entity.git .
    composer install
```
## Usage ##

```bash
Description:
  Convert a JSON representation of a FileMaker layout to a Doctrine entity.

Usage:
  convert [options] [--] <file> <destination> <entity>

Arguments:
  file                  Path to the json file exported from FileMaker.
  destination           Location to save the entity. If you also generate a repo, then they will be put in appropriate sub folders of the location given.
  entity                The name of the entity to generate.

Options:
  -r, --repo[=REPO]     Should a repository also be generated. [default: false]
  -a, --attributes      Use PHP 8+ attributes, rather than comment-based annotations
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
For example

```bash
    bin/console convert ./fields.json ../MySymfonyProject/src Order -r=true -a=false
```
## JSON structure ##

Because this is intended to be used with FileMaker layouts, the table name is referred to as the Layout.

```json
{
    "layout": "Order",
    "fields": [
        {
            "field": "_kRecordID",
            "type": "Number"
        },
        {
            "field": "ClientPO",
            "type": "Text"
        }
    ]
}
```

## TODO ##

 - auto-generate the json by calling a FileMaker script through the FileMaker PHP or Data API
 
## Contact ##

See this [blog post](https://msdev.co.uk/fm-layout-to-entity) for more details.

Steve Winter  
Matatiro Solutions  
steve@msdev.co.uk