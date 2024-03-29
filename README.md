# Filerobot Akeneo Connector by Scaleflex
## Prerequisites
- Create Filerobot Account [Here](https://www.scaleflex.com/request-a-demo)

## Requirement
Application uses Laravel 9 and Livewire
* PHP Version 8.1 and above
* Redis
* PostgresSQL
* For sending Mail, You can create account from Mailgun, Postmarkapp or
any services you want.

## Installation
* Change .env.example to .env
```
#Update DB Connection
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=filekeneo
DB_USERNAME=postgres
DB_PASSWORD=postgres

#Change Queue Connection
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

#Update Mail setting, If you want to send mail
```
* Install packages & Migrate database
```shell
composer install
php artisan migrate
```
* For Application Deployment you can Check this [Document](https://laravel.com/docs/9.x/deployment)
* Application use Horizon to manage Queue, you can use this [Installation Guide](https://laravel.com/docs/9.x/horizon#deploying-horizon)
* Cronjob setting
```shell
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## User guide
### 1. Setup
* Open application on browser and go to ```https://yourdomain/register``` to create an account
  ![register](docs/register.png)
* Then login to the system by go to this link ```https://yourdomain/login```
  ![login](docs/login.png)
* Create new connector by Click ```Add Connector``` and choose your version
  ![add](docs/add.png)
* Fill all information
  ![add-1](docs/add-1.png)
* After submit please wait until ```Setup status``` change from ```Pending``` or ```Processing```
to ```Successfull```
  ![submit](docs/submit.png)

### 2. Config Mapping
After finished setup process, please go to mapping, choose which mapping family you want 
to config
  ![submit](docs/mapping.png)

#### There are two settings

Type Global: Mapping for image without scope and locale specific
   ![global](docs/global.png)
  - Position = Filerobot Product position
  - Akeneo Attribute 
  - Behavior: Override(If any update -> override old file in Akeneo), Ask(If new verion -> Ask for action), Keep old version(Do not update if there is new version)

Type Scope and Locale:
  ![variant](docs/scopelocale.png)
  - Position: Filerobot position
  - Name: Name of tag or variant
  - Type: Tag or Variant
  - Scope: Akeneo Scope(Channel)
  - Locale: Akeneo Locale(Change depend on Scope), If Scope is Null -> All available locales
  - Attribute: Akeneo Attribute, Attribute (will change depend on its config(Value per channel, Value per locale)):
  ![akeneoscope](docs/akeneoscope.png)
  - Behavior: Same as Global type

#### Image size 
You can config image size for each locale and scope, by click Image size in Tab

![filerobot](docs/imagesize.png)

- Scope: Scope from Akeneo
- Locale: Each Scope have a locale to sync
- Size: Image size, Format axb, a and b are intergers

If you use the same size, or system can not find any configuration match specific scope and locale, It will use fallback 
size in Connector Setting

![filerobot](docs/fallback.png)

### 3. Image Product in Filerobot
![filerobot](docs/frproduct.png)
- Product reference: Product or Product Model SKU in Akeneo
- Position: Position in Connector(Use to mapping with Akeneo Attribute)

If you have difference images per Channel or Locale you can use tags and variants
- Difference image per channel or locale: Use Tag
- Difference image size: Use Variant

Name of tags and variant is very important, you want to use it to make mapping with akeneo attribute
Tag
![filerobot](docs/frtag.png)

Variant
![filerobot](docs/frvariant.png)

### 4. Review Assets Sync
You can review assets sync or not to Akeneo by to to Tab ```Products```, you can choose the way asset view
- Product view

  ![byproduct](docs/asbyproduct.png)

- Asset view

  ![byasset](docs/asv.png)

You can
- Search and View Assets by product Code
- Check why asset sync failed
- Check if new version, and change new version sync behavior
- Filter Support
  - By Action: Pending, Override, Keep Old
  - By Status: Sync successful, Sync failed, Not synced yet
  - By Type: Global, Tag, Variant, Null(Special case)
  - Go to configuration for family of product

### 5. Akeneo Enterprise Asset Manager

#### 5.1 Create Meta group on Filerobot
Go to Store -> Metadata Tab, and add meta follow picture bellow(API Value must exact match)
![byasset](docs/meta.png)

- **akeneo_enable**: Boolean type,  Enable sync to Akeneo or not
- **akeneo_family**: Text type, Akeneo Asset manager Attribute family
- **akeneo_attribute**: Text type, Attribute to sync
- **akeneo_scope**: Multiple choices, All scopes from akeneo, 
- **akeneo_locale**: Multiple choices, All locales from akeneo

#### 5.2 Add information in each asset
Each asset click Detail, Metadata tab and add the information
![img.png](docs/assetmeta.png)

#### 5.3 Metadata mapping
Each asset in filerobot can have difference metadata, If you want to sync it to akeneo you can use Meta mapping Tab
![byasset](docs/metamapping.png)

- Metadata: metadata from filerobot
- Family: Family of Asset Manager
- Attribute: Attribute belong to Family
  - If Attribute is Global -> Has Local will uncheck
  - If Attribute has value per channel -> It shows scopes list to choose 
    - You need define difference metadata for each scope like: title_commerce -> attribute title in Scope Commerce
    title_print -> attribute in scope Print
  - If Attribute has value per locale, metadata must have Regional variant when create metadata
    ![byasset](docs/meta_locale.png)
    
    And Language in Filerobot must match the code in Akeneo, You can change it in store setting -> Regional Variant tab
    for Filerobot and Setting in Akeneo
  
    ![byasset](docs/locale_filerobot.png)
    ![byasset](docs/locale_akeneo.png)

## Warning
- If there are any change on Akeneo, You could go to config and Click ```Submit``` to sync new update from akeneo 
to System
![resubmit](docs/resubmit.png)
