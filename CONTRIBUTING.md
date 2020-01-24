# Contributing

Guidelines for working on the project.

## Commit

We standardize the way of writing commits. The goal is to create **messages that are more readable** and that easily pass the project's history.

* Be succinct.
* Always write a title and if necessary a message explaining what was done.
* Writing the reason about a change is better than what was done in the code.
* Standardized language: **English**.

### Formatting

````
[Tag] Relevant title

Commit message. Usually explaining what has changed,
removed or added and possible implementation details
that can be used by the team in future development.
````

### Exemplo de Tags

* **Feat:** A new feature
* **Fix:** Fixing a bug
* **Style:** Change on writing the code
* **Refact:** Refactoring stuff
* **Docs:** Documentation
* **Test:** About tests
* **Build:** About building flow

## Coding Standards

WordPress Coding Standards, with spaces.

## Git

You should use `git rebase master` before merge.

It keeps the git timeline beautiful.

## Development

Install dependencies:

```
npm install
composer install
```

### Lint and Precommit

You can run lint (PHPCS e PHPMD):

```
composer lint
composer phpmd
```

And use git precommit:

```
composer precommit

# Alias for:
# cp pre-commit.sh .git/hooks/pre-commit && chmod +x .git/hooks/pre-commit
```

This will create a precommit to do lint for you.

### Build / Publish

We use @wordpress/scripts and Gulp. So:

```
npm start
gulp watch
```

For publish, we will generate a zip file:

```
npm publish
```

