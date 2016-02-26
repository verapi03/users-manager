# users-manager
Functional basic user management system with sign-up, sign-in, facebook login and user management.

The app uses the MVC architectural pattern developed entirely in CakePHP 2.8. This readme file walks you through three main sections, general structure of the project, the facebook login functionality and final notes about the Admin CRUD module.

General Structure of the Project:

The controller `UsersController` communicates with the models `User` to process data from and for users, or `SocialProfile` to handle facebook login requests. After this communication is done, the controller delegates to a specific view object the task of generating output for the client. This project is based on conventions over configuration, so the controller was named `UsersController`, the model was named `User` and the views are named after the UsersController's methods (`create`, `edit`, etc...).

More specifically, the file `routes.php` was modified to have a custom link for login, logout, the dashboard and for the facebook login controllers. The home page was modified too to point to the login action. So for example, the main page (after the login page) is the dashboard and its route is `domain/dashboard`, which sends a request to the method `index` of the `UsersController` and whose view `index` shows the info of the user or users depending on the role of the user in session. The same logic is applied to `domain/login` and `domain/logout`.

The route `domain/users/edit/id` connects to the method `edit` of `UsersController` to post an update to the user with the id sent.

The route `domain/users/create` lets the admin user to access the `create` method to add a new user to the application. Note that as it was already mentioned the views of the app share the same name with the corresponding action of the controller.

The rest of the `UsersController` is pretty straightforward and a brief documentation about what is every action for was added as a header for every action.

In `User` model is where all the validation logic is, some of them are Cake’s built-in validation rules and some others are custom validation rules: usernames must be non-empty and alphabetic, passwords must have a minimum length of 6 characters, emails must be unique and be at least 6 characters in length and the phone must contain between 7 and 11 numeric characters only. The role of every new user is set automatically to "customer" and can be updated to an agent by an admin.

The Facebook Login Functionality:

In order to handle the facebook login aspect, the HybridAuth PHP library was used (http://hybridauth.sourceforge.net/), an easy to use social login library written in PHP, so this app can be easily scalated to support other common social login as Twiter, linkedIn, Gmail, etc. This library was installed via composer as well as the CakePHP itself.