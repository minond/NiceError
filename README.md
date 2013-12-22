# NiceError

Nicer errors for PHP. Just include `"minond/nice_error": "dev-master"` in your composer file, set the NICE_ERRORS environment variable to "1", and that's it:

```json
{
	"require-dev": {
		"minond/nice_error": "dev-master"
	}
}
```

```text
NICE_ERRORS=1 php your_php_script.php
```

<br />
### Browser output
![screenshot](https://raw.github.com/minond/NiceError/master/resources/readme/browser.png)

### CLI output
![screenshot](https://raw.github.com/minond/NiceError/master/resources/readme/cli.png)

<br />
Inspired by [Better Errors](https://github.com/charliesome/better_errors).
