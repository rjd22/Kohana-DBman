# Dbman Kohana Library.
When working on projects it comes in very handy to be able to version your database. When looking around I couldn't find anything that would suit my needs. This resulted in me building a library for kohana.

## How to start
I included a demo file for experiment purposes. Your migration file go in MODPATH/dbman/migrations by default. You can change this in config/dbman.php. When naming your migration files you need to use this format: [timestamp]_[classname].php

## How to use
When you just want to update your program to the latest version you can just do: 
www.example.com/dbman/update

If you want to update / downgrade to a certain version you can do:
www.example.com/dbman/update/[timestamp]