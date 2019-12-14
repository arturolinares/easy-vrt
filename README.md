**Console tool to generate visual regression tests between two environments. It is
a simple wrapper for the awsome [backstop](https://github.com/garris/BackstopJS)
tool.**

## Requirements

1. Docker
2. PHP 7.2

## Installation

1. `git clone git@github.com:arturolinares/easy-vrt.git`: Get the code.
2. `composer install`: Install this project dependencies.
3. `bin/console vrt:init`: Prepares backstop.

## Commmands

```
console  vrt:init    Prepares backstop worspace on directory "var/backstop/backstop_data".
console  vrt:gen     Generates a backstop config file from a CSV with the routes to test.
console  vrt:run     Runs backstop and builds the html report.
console  vrt:server  Starts a server to browse the results.
```

## Usage

Once the project is installed you will want to compare two sites. Follow these
steps:

1. Generate the backstop scenarios using a CSV file containing the routes to visit.
2. Run the tests.
3. Browse the results to visualize the differences.

To generate the scenarios use the commmand `vrt:gen`. It will use a CSV file
with the routes to generate them:

    bin/console vrt:gen path/to/routes.csv --ref-domain=http://prod.com --url=http://my-qa.com

You can find a sample of the CSV file in the `sample` directory. The file has
two fields: the route and an optional label. For example:

    /,"The Homepage"
    /category/tag,
    "/about-us", "About US"

You can set the reference domain using the flag `--ref-domain`. This is normally
production. Use the flag `--url` to specify the domain you want to compare.

Until now you only have configured backstop. To actually run the tests use
`vrt:run`. This commmand will run backstop using a Docker container. Also, if
you want to see what the script is doing, you can change the verbosity with
`-v`, `-vv` and `-vvv`.

Finally, to see the report, use `vrt:server` to browse the diffs.

## Motivation

I normally work on different and very active projects with several testing
environments. I needed a simple and fast way to generate visual regression
tests between them, or even to just to compare my local environment to an integration
server. I find cumbersome to edit backstop configuration files frequently just to
change servers.

Also, I wanted to test Symfony console libraries :)
