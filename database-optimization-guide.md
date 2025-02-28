# Database Optimization Guide

## Recently Added Indexes

We've added the following indexes to improve query performance:

### Unique Indexes for Slug Columns
- `articles.slug` (unique)
- `article_categories.slug` (unique)
- `article_tags.slug` (unique)
- `books.slug` (unique)
- `book_chapters.slug` (unique)
- `book_groups.slug` (unique)
- `categories.slug` (unique)
- `posts.slug` (unique)
- `posts.source_url` (unique)

### Foreign Key Indexes
- `posts.book_chapter_id`
- `posts.user_id`
- `book_chapters.book_id`
- `books.book_group_id`
- `book_groups.category_id`
- `articles.article_category_id`
- `articles.user_id`
- `categories.parent_id`
- `article_categories.parent_id`
- `article_tag_article.article_id`
- `article_tag_article.article_tag_id`
- `images.imageable_id`
- `images.imageable_type`
- `post_attachments.post_id`

## Verifying Indexes Are Being Used

### Using the EXPLAIN Statement

To verify that MySQL is using the indexes you've created, you can use the EXPLAIN statement:

```sql
EXPLAIN SELECT * FROM posts WHERE slug = 'example-slug';
```

Look for these indicators in the output:
- `key` field should show the name of the index being used
- `rows` field should be a small number (ideally 1 for unique indexes)
- `type` field should ideally be "const", "eq_ref", or "ref" (not "ALL")

### Example Queries to Test

#### Posts by Slug (Should Use Unique Index)
```sql
EXPLAIN SELECT * FROM posts WHERE slug = 'example-slug';
```

#### Posts by Source URL (Should Use Unique Index)
```sql
EXPLAIN SELECT * FROM posts WHERE source_url = 'https://example.com/source';
```

#### Posts by Chapter (Should Use Foreign Key Index)
```sql
EXPLAIN SELECT p.* FROM posts p
JOIN book_chapters bc ON p.book_chapter_id = bc.id
WHERE bc.slug = 'example-chapter';
```

### Using Laravel Query Log

You can also verify indexes through Laravel's query log:

```php
DB::enableQueryLog();
Post::where('slug', 'example-slug')->first();
dd(DB::getQueryLog());
```

## Common Index-Related Issues

### Composite Indexes

If you frequently query with multiple conditions, consider creating composite indexes:

```php
// Migration example
$table->index(['book_chapter_id', 'created_at']);
```

This works well for queries like:
```php
Post::where('book_chapter_id', $chapterId)->orderBy('created_at', 'desc')->get();
```

### Ineffective Indexes

Indexes may not be used if:
1. You're using functions on indexed columns: `WHERE YEAR(created_at) = 2023`
2. You're using LIKE with a leading wildcard: `WHERE name LIKE '%test%'`
3. You're using OR conditions between different columns

### Index Maintenance

Indexes improve query speed but can slow down INSERT, UPDATE, and DELETE operations. Monitor your database performance regularly to ensure the indexes are providing more benefit than cost.

## Further Performance Optimizations

### Query Caching

For frequently accessed data, implement caching:

```php
// Example using Laravel's cache system
$posts = Cache::remember('recent_posts', 3600, function () {
    return Post::latest()->take(10)->get();
});
```

### Eager Loading

Use eager loading to prevent N+1 query problems:

```php
// Instead of:
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->chapter->name; // Causes a separate query for each post
}

// Use:
$posts = Post::with('chapter')->get(); // Single query with eager loading
```

### Pagination

For large datasets, always use pagination:

```php
$posts = Post::latest()->paginate(20);
```

## Monitoring Tools

1. **MySQL Slow Query Log**: Enable to identify problematic queries
2. **Laravel Telescope**: Provides insights into queries if you're in development
3. **Percona Monitoring and Management (PMM)**: For production monitoring
4. **Laravel Debugbar**: Shows queries and their execution time during development

## Index Management Commands

```
# List all indexes on a table
SHOW INDEX FROM posts;

# Add an index in MySQL
ALTER TABLE table_name ADD INDEX index_name (column_name);

# Remove an index in MySQL
ALTER TABLE table_name DROP INDEX index_name;
```

Remember, database optimization is an ongoing process. Regularly monitor performance and adjust indexes as your application's query patterns evolve.
