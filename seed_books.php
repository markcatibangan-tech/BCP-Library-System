<?php
include 'config.php';

$sample_books = [
    ['title' => 'Introduction to C++ Programming', 'author' => 'Y. Daniel Liang', 'category' => 'Computer Engineering', 'quantity' => 5],
    ['title' => 'Java Programming: From Problem Analysis to Program Design', 'author' => 'D.S. Malik', 'category' => 'Computer Engineering', 'quantity' => 5],
    ['title' => 'Data Structures and Algorithms in Java', 'author' => 'Robert Lafore', 'category' => 'Computer Science', 'quantity' => 5],
    ['title' => 'Cisco Certified Network Associate (CCNA) Study Guide', 'author' => 'Todd Lammle', 'category' => 'Networking', 'quantity' => 5],
    ['title' => 'Digital Logic and Computer Design', 'author' => 'M. Morris Mano', 'category' => 'Computer Engineering', 'quantity' => 5],
    ['title' => 'Microprocessor Architecture and Microcontrollers', 'author' => 'Ramesh S. Gaonkar', 'category' => 'Computer Engineering', 'quantity' => 5],
    ['title' => 'Database System Concepts', 'author' => 'Abraham Silberschatz', 'category' => 'Information Technology', 'quantity' => 5],
    ['title' => 'Operating System Concepts', 'author' => 'Abraham Silberschatz', 'category' => 'Computer Science', 'quantity' => 5],
    ['title' => 'Computer Networks', 'author' => 'Andrew S. Tanenbaum', 'category' => 'Networking', 'quantity' => 5],
    ['title' => 'HTML and CSS: Design and Build Websites', 'author' => 'Jon Duckett', 'category' => 'Web Development', 'quantity' => 5],
    ['title' => 'JavaScript and JQuery: Interactive Front-End', 'author' => 'Jon Duckett', 'category' => 'Web Development', 'quantity' => 5],
    ['title' => 'PHP and MySQL for Dynamic Web Sites', 'author' => 'Larry Ullman', 'category' => 'Web Development', 'quantity' => 5],
    ['title' => 'Modern Control Engineering', 'author' => 'Katsuhiko Ogata', 'category' => 'Engineering', 'quantity' => 5],
    ['title' => 'Calculus: Early Transcendentals', 'author' => 'James Stewart', 'category' => 'Mathematics', 'quantity' => 5],
    ['title' => 'Technical Communication Today', 'author' => 'Richard Johnson-Sheehan', 'category' => 'General Education', 'quantity' => 5]
];

$added = 0;
foreach ($sample_books as $b) {
    $title = $conn->real_escape_string($b['title']);
    $author = $conn->real_escape_string($b['author']);
    $category = $conn->real_escape_string($b['category']);
    $quantity = $b['quantity'];

    // Check if book already exists to prevent duplicate entries
    $check = $conn->query("SELECT * FROM books WHERE title = '$title'");
    if ($check && $check->num_rows == 0) {
        $conn->query("INSERT INTO books (title, author, category, quantity, status) VALUES ('$title', '$author', '$category', $quantity, 'Available')");
        $added++;
    }
}

echo "<h3>Successfully added $added new books to the catalog!</h3>";
echo "<a href='dashboard.php'>Go to Student Portal</a> | <a href='admin.php'>Go to Admin Portal</a>";
?>