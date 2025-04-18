# Amadeco SmileCustomEntityLayeredNavigation Module for Magento 2

[![Latest Stable Version](https://img.shields.io/github/v/release/iparmentier/magento2-smile-custom-entity-layered-navigation)](https://github.com/iparmentier/magento2-smile-custom-entity-layered-navigation/releases)
[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/github/license/iparmentier/magento2-smile-custom-entity-layered-navigation)](iparmentier/magento2-smile-custom-entity-layered-navigation/blob/main/LICENSE.txt)

[SPONSOR: Amadeco](https://www.amadeco.fr)

## Overview

The Amadeco SmileCustomEntityLayeredNavigation module enhances Magento 2 by adding layered navigation (faceted search) capabilities to Smile Custom Entities. This module mirrors Magento 2's native layered navigation behavior, applying the same familiar filtering interface to custom entities. It enables customers to filter custom entity collections through an intuitive interface, improving the user experience and helping them find relevant content more efficiently.

## Features

- **Layered Navigation for Custom Entities**: Adds faceted search to Smile Custom Entity collections, following Magento 2's native layered navigation behavior and conventions
- **Filterable Attributes**: Configure any custom entity attribute to be filterable in the navigation
- **SEO Optimization**: Automatically adds NOINDEX, FOLLOW meta robots tag to filtered pages
- **Custom Sorting Options**: Sort entities by attributes like name, creation date, and custom fields
- **Pagination Controls**: Enhances the entity listing with pagination functionality
- **Multiple Filter Types**: Supports various attribute types (select, multiselect, boolean) as filters
- **Performance Optimized**: Custom indexer for efficient filter operations on large collections

## Installation

### Composer Installation

Execute the following commands in your Magento root directory:

```bash
composer require amadeco/module-smile-custom-entity-layered-navigation
bin/magento module:enable Amadeco_SmileCustomEntityLayeredNavigation
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento indexer:reindex amadeco_smile_custom_entity_layer_set
bin/magento setup:static-content:deploy
```

### Manual Installation

1. Create directory `app/code/Amadeco/SmileCustomEntityLayeredNavigation` in your Magento installation
2. Clone or download this repository into that directory
3. Enable the module and update the database:

```bash
bin/magento module:enable Amadeco_SmileCustomEntityLayeredNavigation
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento indexer:reindex amadeco_smile_custom_entity_layer_set
bin/magento setup:static-content:deploy
```

## Usage

After installation, the layered navigation functionality will be automatically added to custom entity set pages after some attributes are configured to be filterable.

### Configuration

1. Navigate to **Stores > Attributes > Custom Entity** in the Magento Admin Panel
2. Edit any existing attribute or create a new one
3. In the "Layered Navigation Configuration" section:
   - Set "Use in Layered Navigation" to:
     - "No" - Attribute will not be used for filtering
     - "Filterable (with results)" - Only shows options with matching entities
     - "Filterable (no results)" - Shows all options regardless of results
   - Set "Position" to control where the filter appears in the navigation menu

## Requirements

- Magento 2.4.x
- Smile CustomEntity module (https://github.com/Smile-SA/magento2-module-custom-entity)
- Smile ScopedEav module (https://github.com/Smile-SA/magento2-module-scoped-eav)

## Compatibility

- Magento 2.4.x
- PHP 8.3

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues or feature requests, please create an issue on our GitHub repository.

## License

This module is licensed under the Open Software License ("OSL") v3.0. See the [LICENSE.txt](LICENSE.txt) file for details.

## Credits

Developed by [Ilan Parmentier](https://github.com/iparmentier) for [Amadeco](https://www.amadeco.fr).
