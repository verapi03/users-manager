# users-manager
Functional basic user management system with sign-up, sign-in and user management.

The app uses the MVC architectural pattern developed entirely in CakePHP 2.8. The basic structure of the project is the following:

The controller UsersController communicates with the model User to process data from and for users or with SocialProfile to handle facebook login requests, after this communication is done, the controller delegates to a specific view object the task of generating output for the client. This project is based on conventions over configuration, so the controller was named UsersController, the model was named User and the views are named after the UsersController's methods (create, edit, etc...).

More specifically, the file routes.php was modified to have a custom link for login, logout, the dashboard and for the facebook login controllers. The home page was modified too to point to the login action. So for example, the main page (after the login page) is the dashboard and its URL is `domain/dashboard`, which sends a request to the method `index` of the UsersController and whose view `index` generates the info of the user or users depending on the role of the user in session. The same logic is apllied to `domain/login` and `domain/logout`.


