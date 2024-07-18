# Armada_ProductImport module

This module provides a framework and basic functionality for importing product data

## Installation

The Armada_ProductImport module creates the following tables in the database:

- `armada_product_import_history`
- `armada_product_import_log`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.


## Structure

`Files/` - the directory that contains sample import files.


## Extensibility

Extension developers can interact with the Armada_ProductImport module.

### Layouts

This module introduces the following layout handles in the `view/frontend/layout` directory:

- `armada_import_log`
- `armada_import_product`
- `armada_import_validate`


### UI components

You can extend an import updates using the configuration files located in the `view/armada/ui_component` directory:

- `product_import_grid`
- `product_import_log`


## Additional information

#### Message Queue Consumer

- `productimport.import` - consumer to run import process
