# Translator Microservice

A PHP Microservice for managing Translations. You can use it to *download* translations.

# Usage
## Install
To install project you should copy it via "git clone git@github.com:crudie/translator-microservice.git", then copy and rename config/app.json.dist file to app.json and specify your settings.
## Show all available locales

To see all locales available just make the following request:

    GET /locales
    
and you will get a JSON response like this:

```json
{
  "response": [
    {
      "name": "ru"
    },
    {
      "name": "en"
    }
  ],
  "ver": "0.1",
  "error_code": 0,
  "error_message": "ok",
  "time": "2016-11-28 17:48:34"
}
```

## Show all translations for a given locale

To see all translation for a given locale just do:

    GET /translations/ru
    
The result will be something like this:

```json
{
  "response": [
    {
      "key": "hello",
      "translation": "привет"
    },
    ...
  ],
  "ver": "0.1",
  "error_code": 0,
  "error_message": "ok",
  "time": "2016-11-28 17:50:15"
}
```

## Show single or multiple translations for a given locale

To see single or multiple translations for a given locale just send request to:

    GET /translations/ru/hello&bye
    
When & is a separator.
The result will be like this one:

```json
{
  "response": [
    {
      "key": "hello",
      "translation": "привет"
    },
    {
      "key": "bye",
      "translation": "пока"
    }
  ],
  "ver": "0.1",
  "error_code": 0,
  "error_message": "ok",
  "time": "2016-11-28 17:50:15"
}
```

## Tests run

The project have functional and unit tests. 

In order to run them, copy and rename phpunit.xml.dist, specify "app_dir" and "site_url" variables inside it to your local path and url, and then run "phpunit src" to run tests.

## Future

There will be docker integration in few days :)
