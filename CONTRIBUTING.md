# Contributing to Baseplate Core

First off, thank you for considering contributing to Baseplate Core! It's people like you that make Baseplate Core such a great tool.
When contributing to this repository, please first discuss the change you wish to make via [issues board](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues) or any other method with the owners of this repository before making a change.

### Code of Conduct
This project and everyone participating in it is governed by the Baseplate Core Code of Conduct. By participating, you are expected to uphold this code.

<!--
## Code of Conduct
We expect everyone contributing to Baseplate Core to follow our [Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/). Please read it before participating.
 -->

### What we are looking for
We love contributions, but we are particularly interested in contributions that:
- Fix bugs or errors
- Improve efficiency
- Address issues that are critical to the operation
- Add new features that are beneficial to users

## Getting Started
For setting up your local development environment, please follow the setup instructions in the [README.md](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/blob/main/README.md) file.

## Issue Tracking
We track our issues on GitLab at:
- https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues

If you find an issue, check if it's already been reported. If not, open a new one. Be sure to include a title and clear description, as much relevant information as possible, and a code sample or an executable test case demonstrating the expected behavior that is not occurring.

#### Reporting Bugs
Bugs are tracked as [GitLab issues](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues). If you found a bug in the Baseplate Core, ensure it is not already listed. If not, you can open a new issue providing the following information:

- A clear and descriptive title.
- A detailed description of the bug.
- Steps to reproduce the behavior.
- Expected behavior.
- Screenshots (if applicable).
- Additional context (environment, version).
- Label issue as `bug`.

#### Suggesting Enhancements
Enhancement suggestions are also tracked as [GitLab issues](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues). Before opening a new suggestion, please check if it's already listed or discussed. When suggesting enhancements, include:

- A clear and descriptive title.
- A detailed explanation of the suggested enhancement.
- Any additional information or context like usage scenarios or benefits.
- Label issue as `enhancement`.

## Core and Module Merge Request Process
1. Ensure you have set up your local environment and it's functioning correctly.
2. Update your baseplate-core local repository to the latest main branch `git pull origin main` and create a new branch for your feature or fix.
3. Make your changes, including appropriate test cases and documentation.
4. Format your code according to the project's coding standards.
5. Commit your changes, ensuring your commit messages are clear and follow the project's guidelines.
6. Push your branch to GitLab.
7. Open a new pull request against the `main` branch as draft **(ensure delete branch is checked)**. Provide a clear and detailed title and description of the changes.
8. And, Open a new pull request against the `demo-build` branch as draft. Provide a clear and detailed title and description of the changes.
9. Wait for a code review and address any comments or changes requested.

## Creating New Module
1. Ensure you have set up your local environment and it's functioning correctly.
2. Update your baseplate-core local repository to the latest main branch `git pull origin main` and create a new branch for your feature or fix.
2. Run `php artisan lego:create NewModuleName`
3. Navigate to gitlab and create a new project under experimental, make it a blank project
    - https://gitlab.com/appetiser/baseplate/backend/experimental
4. Your new project will contain some instructions. follow the "Push an existing folder" instructions:
    ```
    Note:
    Replace the "cd existing_folder" to your module folder i.e. "cd modules/NewModuleName"
    ```
5. Star a label to make it a priority label.
    ```
    - bug
    - confirmed
    - critical
    ```
5. Commit your changes, ensuring your commit messages are clear and follow the project's guidelines.

#### When you want your changes to be included in the deployment in `demo-build`.
- Create a new issue on the baseplate-core [issues board](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues).
- Use the `New Module` template and indicate details and mark your label as `module`, and click create button.
- If possible ping the `#web_team` channel in slack about the newly created issue with the link.

## Getting Help
If you have a specific question about using Baseplate Core,first check the [Issues](https://gitlab.com/appetiser/baseplate/backend/baseplate-core/-/issues) to see if it's already being discussed. If not, feel free to open a new discussion or issue with a clear description of your problem or question. This helps us provide you with the most relevant assistance efficiently.
