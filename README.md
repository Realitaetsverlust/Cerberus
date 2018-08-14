Cerberus
==========

### A code modifier tool written in the worst language of them all

Cerberus is a super simple and extendable code modifier tool written in PHP. What does this do? You can setup tags and modifiy code based on the rules you can program yourself. For example, lets say we have the following code:

```
public function foo() {
    $i = 0;
    $i += 1;

    //#DEBUG::START
    var_dump($i);
    //#DEBUG::END

    return $i;
}
```

As you can see, we have two tags there: #DEBUG::START and #DEBUG::END. These indicate that these lines are to be affected by the filter `debug`.

A filter is as simple as you can possible imagine:

```
namespace Cerberus;

class debug {
    public function disable($line) {
        return '//'.$line;
    }
}
```

That's literally everything you need in order to comment every line you get. We can now execute

`php /etc/Cerberus/execute.php debug::disable /var/www/html/yourRepoToCheck/`

or, if you setup an alias like `alias cerberus='php /etc/Cerberus/execute.php'`

`cerberus debug:disable /var/www/html/yourRepoToCheck`

and the file will look like this:

```
public function foo() {
    $i = 0;
    $i += 1;

    //#DEBUG::START
//    var_dump($i);
    //#DEBUG::END

    return $i;
}
```

This is a great and quick way to make sure none of your debugs are actually having an impact. Obv, you can also remove the line, transform them - literally everything is possible.

Adding a new filter is as simple as adding a new file in the `Filters` folder, naming the file and the class accordingly. So, if you want a filter like `remove::clear`, your code will look like the following:

Filename: remove.php
```
namespace Cerberus;

class remove {
    public function clear($line) {
        //your logic here
    }
}
```

## Requirements:
 - I only tested it on PHP 7.0, but I did not use any features PHP 7.0+ do offer. You will most likely need PHP 5.6, otherwise array declarations will fail.
 - Git (Cerberus is using 'git status' to figure out what files were modified or added and will only check those)
 - That's it!

## ToDos:

- Adding an `--all` parameter to skip the `git status` execution and just check the entire folder.
- Adding an verbose option, I actually got it in the code already but it's not used yet.

## FAQ:
Q: Why?

A: Because I tend to forget my dumps in tools. If you don't, you're probably a good programmer and won't need this.

Q: Why PHP?

A: Because my Python is pretty rusty and I wanted some quick results.