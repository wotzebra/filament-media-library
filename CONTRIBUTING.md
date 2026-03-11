# CONTRIBUTING

## New Features or bug fixes

We love pull requests from everyone. By participating in this project, you agree to abide by our [code of conduct](CODE_OF_CONDUCT.md).

[Fork](https://docs.github.com/en/get-started/quickstart/fork-a-repo), then clone the repo:

```bash
git clone git@github.com:your-username/filament-media-library.git
```

Install the composer packages:

```bash
composer install
```

Make sure the tests pass:

```bash
vendor/bin/pest
```

Make your change. Add tests for your change.

Push to your fork and submit a pull request.

At this point you're waiting on us. We may suggest some changes or improvements or alternatives.

Some things that will increase the chance that your pull request is accepted:

-   Write tests.
-   Follow our style guide.
-   Document any change in behaviour - Make sure the README.md and any other relevant documentation are up-to-date.
-   Write a good commit message.

## Submitting an issue

Some things to consider before submitting an issue

-   Search the existing issues to see if it already has been asked or reported.
-   If you do find a similar issue, upvote it by adding a 👍 reaction . If you have relevant information to add, do so in a comment. Please don't add a `+1` comment.
-   When making an issue, follow the issue templates and provide as much information as possible, even better try to provide a repository demonstrating the issue.

## Coding style

### Laravel Pint

Don't worry if your code styling is not perfect! Each of our packages have a [Laravel Pint](https://github.com/laravel/pint) Github Action will automatically merge any style fixes into the package when you commit changes to a pull request.
