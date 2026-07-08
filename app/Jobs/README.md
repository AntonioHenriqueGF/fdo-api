# Jobs, Queues and Supervisor

Jobs are a way to run time-consuming tasks asynchronously in the background. This allows your application to respond to user requests more quickly by offloading tasks that can be processed later.

When executed on the code, they are sent to a queue, which is a storage mechanism that holds jobs until they are processed by a worker. Workers are processes that listen to the queue and execute the jobs as they come in.

The command `php artisan queue:work` is used to start a worker that will process jobs from the queue. You can run multiple workers to handle more jobs concurrently. In production environments, it is common to use a process manager like Supervisor to ensure that your workers are always running and to manage their lifecycle.

Please, refer to the official Laravel documentation on [Supervisor Configuration](https://laravel.com/docs/12.x/queues#supervisor-configuration) for more details on how to set up and configure jobs, queues, and workers in your application.