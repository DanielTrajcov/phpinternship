<?php
require 'components/Database.php';
require 'components/User.php';
require 'components/Vote.php';
require 'components/Category.php';

session_start();

$db = new Database();
$user = new User($db);
$vote = new Vote($db);
$category = new Category($db);

$defaultSection = 'winners';
if (!isset($_GET['winners']) && !isset($_GET['vote'])) {
    header("Location: ?$defaultSection");
    exit;
}

$voteResults = null;
$mostFrequentVoters = null;

if (strpos($_SERVER['REQUEST_URI'], 'winners') !== false) {
    // Fetch vote results and frequent voters
    $voteResults = $vote->getVoteResults();
    $mostFrequentVoters = $vote->getMostFrequentVoters();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    if (!isset($_SESSION['username'])) {
        echo json_encode(["status" => "error", "message" => "You must be logged in to vote."]);
        exit;
    }

    $voter_username = $_SESSION['username'];
    $nominee_username = $_POST['nominee_username'];
    $category_name = $_POST['category_name'];
    $comment = $_POST['comment'];

    $response = $vote->submitVote($voter_username, $nominee_username, $category_name, $comment);
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script/main.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="script/style.css" rel="stylesheet">

</head>
<body>

<nav class="text-2xl">
  <div class="max-w-7xl mx-auto px-2 sm:px-8 lg:px-6">
    <div class="relative flex items-center justify-between h-16 ">
        <a href="#" class="text-white  text-3xl font-bold">
          Vote<span class="accent">Hub</span>
        </a>

        <div class="flex items-center gap-2">
      <div class="flex px-2">
        <a href="?winners" class="text-white">
          Results
        </a>
      </div>

        <div>
          <a href="?vote">
            <button class="bg-accent text-black py-1 px-6 rounded-xl ">
              Vote
            </button>
          </a>
        </div>

        <?php if (isset($_SESSION['username'])) { ?>
        <div>
          <a href="components/Logout.php">
        <svg class="w-8 h-8 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
        </svg>
        </a>
        </div>
        <?php } ?>

      </div>
    </div>
  </div>
</nav>


    <?php
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    if (strpos($url, 'winners') !== false) { ?>

<div class="flex flex-col items-center justify-center space-y-8 py-10">
    <?php if ($voteResults && $voteResults->num_rows > 0): ?>
        <?php 
        $categoryResults = [];
        while ($row = $voteResults->fetch_assoc()) {
            $categoryResults[$row['category_name']][] = $row;
        }
        ?>
        <?php foreach ($categoryResults as $category => $results): ?>
            <div class="text-center">
                <h3 class="text-xl font-semibold mb-4">Category: <?= htmlspecialchars($category); ?></h3>
                <table class="w-[400px] text-sm text-white text-center border rounded-lg overflow-hidden mx-auto" style="box-shadow: 0px 0.5px 3px #0f9;">
                    <thead class="text-xs accent uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nominee</th>
                            <th scope="col" class="px-6 py-3">Total Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr class="bg-gray-900">
                                <td class="px-6 py-4"><?= htmlspecialchars($result['nominee_username']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($result['total_votes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-gray-400">No votes yet.</p>
    <?php endif; ?>

    <h2 class="text-xl font-semibold mb-4">Most Frequent Voters</h2>
    <?php if ($mostFrequentVoters && $mostFrequentVoters->num_rows > 0): ?>
        <div class="text-center">
            <table class="w-[400px] text-sm text-white text-center border rounded-lg overflow-hidden mx-auto" style="box-shadow: 0px 0.5px 3px #0f9;">
                <thead class="text-xs accent uppercase">
                    <tr>
                        <th scope="col" class="px-6 py-3">Voter</th>
                        <th scope="col" class="px-6 py-3">Total Votes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $mostFrequentVoters->fetch_assoc()): ?>
                        <tr class="bg-gray-900">
                            <td class="px-6 py-4"><?= htmlspecialchars($row['voter_username']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['total_votes']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-400">No frequent voters yet.</p>
    <?php endif; ?>
</div>


    <?php 
    } else if (strpos($url, 'vote')) {
        // Check session only for the vote section
        if (!isset($_SESSION['username'])) { ?>
           
            <?php if (isset($message)) echo $message; ?>


          <section id="login-form" class="form-container active">
            <div class="flex items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <div class="w-full rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold text-white md:text-2xl ">
                            Sign in to vote
                        </h1>
                        <form class="space-y-4 md:space-y-6" method="POST" action="components/auth.php">
                        <input type="hidden" name="action" value="login">
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-white">Your email</label>
                                <input type="email" name="email" id="email" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="name@email.com" required="">
                            </div>
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-white">Password</label>
                                <input type="password" name="password" id="password" placeholder="••••••••" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" required="">
                            </div>
                            <button type="submit" class="w-full text-black bg-accent font-medium rounded-lg text-sm px-5 py-2.5 text-center">Sign in</button>
                            <p class="text-sm font-light text-gray-500">
                                Don’t have an account yet? 
                                
                                <a href="#" class="font-medium text-primary-600 hover:underline" onclick="showForm('register')">Sign up</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
          </section>

          <section id="register-form" class="form-container ">
            <div class="flex items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <div class="w-full rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold text-white md:text-2xl ">
                            Register
                        </h1>
                        <form class="space-y-4 md:space-y-6" method="POST" action="components/auth.php">
                        <input type="hidden" name="action" value="register">
                            <div>
                                <label for="username" class="block mb-2 text-sm font-medium text-white">Username</label>
                                <input type="text" name="username" id="username" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="username" required="">
                            </div>
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-white">Your email</label>
                                <input type="email" name="email" id="email" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" placeholder="name@email.com" required="">
                            </div>
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-white">Password</label>
                                <input type="password" name="password" id="password" placeholder="••••••••" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" required="">
                            </div>
                            <div>
                                <label for="password_confirm" class="block mb-2 text-sm font-medium text-white">Confirm Password</label>
                                <input type="password" name="password_confirm" id="password_confirm" placeholder="••••••••" 
                                class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5" required="">
                            </div>
                            <button type="submit" class="w-full text-black bg-accent font-medium rounded-lg text-sm px-5 py-2.5 text-center">Register</button>
                            <p class="text-sm font-light text-gray-500">
                                Already have account ?
                                
                                <a href="#" class="font-medium text-primary-600 hover:underline" onclick="showForm('login')">Sign in</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
          </section>
        
       
        <?php
        } else {
            $employees = $category->fetchOptions('users', 'username');
            $categories = $category->fetchOptions('categories', 'name');
            ?>
            
            <section>
            <div class="flex items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <div class="w-full rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold text-white md:text-2xl ">
                            Vote
                        </h1>
                        <form class="space-y-4 md:space-y-6" id="voteForm">
                            <div>
                                <label for="nominee_username" class="block mb-2 text-sm font-medium text-white">Nominee</label>
                                <select name="nominee_username" required 
                                  class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5">
                                  <?php while ($row = $employees->fetch_assoc()): ?>
                                  <option value="<?= $row['username'] ?>"><?= $row['username'] ?></option>
                                  <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label for="category_name" class="block mb-2 text-sm font-medium text-white">Category</label>
                                <select name="category_name" required 
                                  class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5">
                                  <?php while ($row = $categories->fetch_assoc()): ?>
                                  <option value="<?= $row['name'] ?>"><?= $row['name'] ?></option>
                                  <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="block mb-2 text-sm font-medium text-white">Comment</label>
                                <textarea name="comment" required 
                                  class="bg-gray-700 border border-gray-600 text-white rounded-lg block w-full p-2.5">
                                  </textarea>
                            </div>

                            <button type="submit" class="w-full text-black bg-accent font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit Vote</button>
                        </form>
                    </div>
                </div>
            </div>
          </section>


    <?php
        }
    }
    ?>
</body>
</html>