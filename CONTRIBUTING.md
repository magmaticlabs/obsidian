# Contribution Guide  

This document serves as a guideline for contributing to the Obsidian project. 
We welcome contributions in any form, including bug reports, new feature 
requests, or pull requests! When submitting your contribution, please 
follow these guidelines in order to make our lives easier, and increase the 
liklihood of your contribution being accepted. 

## Bug Reports & Feature Requests

Bug reports and feature requests should be submitted as [issues](https://github.com/magmaticlabs/obsidian/issues) 
on the project repository. Please check to make sure your bug or feature does 
not already have an existing issue before you create one. Try to be as specific 
as possible in your bug report or feature request. At minimum, your should 
include the following information: 

_Bug Report_  
 - What you were doing when the bug happened
 - What you expected to happen
 - What happened instead
 - Steps to reproduce (optional, but VERY helpful)  

_Feature Request_ 
 - A brief description of the feature
 - Why it is useful, and who can make use of the feature  

Please do not include any tags or labels with your issue. Maintainers will 
apply appropriate labels. 

## Pull Requests

When submitting your PR, please adhere to the following: 

- Submit pull requests from a topic branch on your fork (e.g `feature-awesome-new-thing`)
- Submit pull requests against the `development` branch, NOT `master`
- Rebase your pull request against the `development` branch before you submit it
- Code should be formatted correctly. (see below)  
- If you are adding a new feature, it should have tests
- The ENTIRE test suite should pass

## Code Standard

This project uses a modified version of the [Symfony Coding Standard](https://symfony.com/doc/current/contributing/code/standards.html).  
You can view the [`.php_cs`](.php_cs) file for the specific changes.  

Use the php-cs-fixer tool (included in the dev tools for the project) to fix up  
any style changes before submitting a PR. 


## Branches

- `master`  - the latest, stable, production-ready code   
- `development` - latest accepted code changes, stable, but prone to further changes before release  

Other branches are usually topic branches and/or unstable features and changes.