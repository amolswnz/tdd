### Instructions for an Expert Silverstripe Developer

As an expert Silverstripe developer, I follow a set of strict guidelines to ensure code quality, consistency, and maintainability. These instructions are designed to produce a robust, scalable, and secure application.

***

### 1. Code Structure and Naming Conventions

* **Follow PSR-12 and Silverstripe's own coding standards.** All PHP code must adhere to these conventions.
* **Use clear, descriptive naming.** Classes, methods, and variables should be self-documenting. For example, a method that gets a list of featured articles should be named `getFeaturedArticles()`, not `get_articles()` or `getArt()`.
* **Organize files logically.** Place custom classes, DataObjects, and controllers in their respective directories within the `app` or a custom module. Templates should be in `themes/your-theme-name/templates/`, with page layouts in the appropriate namespace subdirectory and reusable partials in `Includes`.
* **Keep templates clean.** Business logic, database queries, and complex calculations should not be in `.ss` template files. Templates are for presentation only.

***

### 2. Data Models and Database Management

* **Design DataObjects carefully.** Use the `$db`, `$table_name`, `$has_one`, `$has_many`, and `$many_many` static properties to define your data schema.
* **Implement data validation.** Use `getCMSValidator()` on your DataObjects to ensure data integrity before it's saved. For forms, use a `RequiredFields` validator.
* **Query efficiently.** Use `DataList` methods like `filter()`, `exclude()`, `sort()`, and `limit()` to retrieve data. Avoid using raw SQL queries unless absolutely necessary and always use parameterized queries to prevent SQL injection.

***

### 3. Controller and Routing

* **Separate concerns.** Controllers should handle the business logic and retrieve data, then pass it to the template for rendering.
* **Use the `UrlSegment` to define custom routes.** This allows for clean URLs and better search engine optimization.
* **Implement appropriate access control.** Use `canView()`, `canEdit()`, `canCreate()`, and `canDelete()` methods on your DataObjects and Pages to manage user permissions.

***

### 4. Security and Performance

* **Sanitize all user input.** Silverstripe's form fields and ORM provide built-in protection against common vulnerabilities like XSS and SQL injection. Always use these built-in methods.
* **Cache aggressively.** Use Silverstripe's built-in caching mechanisms, such as `$cache` for complex function returns, to reduce database queries and improve performance.
* **Optimize frontend assets.** Use build tools like **Webpack** or **Vite** to compile, minify, and version your CSS and JavaScript files.
* **Check for dependencies.** Regularly update your project's dependencies using `composer update` to apply security patches and new features.


### General guidelines

---

### Folder structure
As an expert Silverstripe developer, I will adhere to the following file organization principles. This guide will help me determine the correct location for each file, ensuring a logical, maintainable, and consistent project structure.

### Back-end PHP Files (`src`)

* **`src`**: All custom PHP classes go here.
* **`src/Admin`**: Use this folder for classes that extend `ModelAdmin`, which are used to manage custom data types in the CMS.
* **`src/Controllers`**: Place custom page controllers here, or controllers that handle specific URL routes.
* **`src/Models`**: This is for `DataObject` classes that don't represent a page. These are things like articles, team members, or products.
* **`src/PageTypes`**: This folder is for classes that extend the `Page` class. Each file in here should represent a unique page type within the CMS, such as `HomePage.php` or `ArticlePage.php`.
* **`src/Extensions`**: Use this folder for `DataExtension` classes, which are used to add new fields or functionality to existing classes without modifying the core files.
* **`src/Tasks`**: Store classes for one-off commands that extend `BuildTask`, such as data migrations or maintenance scripts.
* **`src/Elemental`**: For Silverstripe Elemental projects, this folder is where you'll keep custom block classes (`BaseElement.php`).
* **`src/Helpers`** and **`src/Traits`**: These folders are for utility classes and reusable traits that don't fit into the other categories.
* **`tests`**: All unit and functional tests for the back-end code should be placed here, mirroring the `src` folder's structure.
* **`fixtures`**: Use this folder for any YAML files containing test data for your tests.

---

### Front-end Assets (`themes/app`)

* **`themes/app/src`**: This is for all **raw, uncompiled assets**.
    * **`src/js`**: Store uncompiled JavaScript files here.
    * **`src/scss`**: All Sass/SCSS files go here. Use the numbered folders to ensure a logical cascade of styles.
        * `00_settings`: Global variables and settings.
        * `10_functions` and `20_mixins`: Reusable code snippets.
        * `30_frameworks`: Third-party framework overrides.
        * `40_base`: Base HTML element styling.
        * `50_templates`: Styling for page-level templates.
        * `60_blocks` and `70_components`: Reusable block and component styles.
        * `80_third-party`: Styling for other modules.
        * `90_overrides`: Final overrides.
* **`themes/app/dist`**: This folder is for **compiled, production-ready assets**. It is typically generated by a build process (like Webpack or Vite) and should not be manually edited.
* **`themes/app/templates`**: This is for all **Silverstripe `.ss` template files**.
    * **`templates/Layout`**: This is the default location for basic page layout templates like `Page.ss`.
    * **`templates/App/PageTypes/Layout`**: This is for custom page type templates that match the namespace structure. For example, `HomePage.ss` for `App\PageTypes\HomePage` class.
    * **`templates/App/Controllers`**: For custom controller templates that match the namespace structure.
    * **`templates/Includes`**: Place small, reusable template partials here, such as headers, footers, or navigation menus.
    * **`templates/App`**: Custom template layouts for your own modules or specific design elements.
    * **`templates/DNADesign/Elemental`**: Templates for the Elemental blocks, with layouts for each block type.
