
CREATE DATABASE IF NOT EXISTS carl;
USE carl;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_progress table
CREATE TABLE IF NOT EXISTS user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    current_level INT DEFAULT 0,
    score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create challenges table
CREATE TABLE IF NOT EXISTS challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    difficulty VARCHAR(10) NOT NULL,
    description TEXT NOT NULL,
    buggy_code TEXT NOT NULL,
    correct_code TEXT NOT NULL,
    hint TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add index for better performance
CREATE INDEX idx_user_progress_user_id ON user_progress(user_id);


ALTER TABLE users
ADD COLUMN social_id VARCHAR(255) NULL,
ADD COLUMN social_provider VARCHAR(20) NULL;

-- Add composite index for social login lookups
CREATE INDEX idx_social_login ON users(social_provider, social_id);

-- Insert challenge data
INSERT INTO challenges (difficulty, description, buggy_code, correct_code, hint) VALUES

('easy', 'Fix the syntax error in this JavaScript function that calculates the sum of two numbers.',
'function add(a, b {\r\n  return a + b\r\n}',
'function add(a, b) {\r\n  return a + b;\r\n}',
'Check the function parameters - are all parentheses properly closed?'),

('easy', 'This loop should print numbers from 1 to 5, but it''s not working correctly.',
'for (let i = 1; i < 5; i++) {\r\n  console.log(i);\r\n}',
'for (let i = 1; i <= 5; i++) {\r\n  console.log(i);\r\n}',
'Look at the loop condition. Will it include the number 5?'),

('medium', 'Fix the array manipulation code to properly reverse the elements.',
'function reverseArray(arr) {\r\n  for (let i = 0; i < arr.length; i++) {\r\n    arr[i] = arr[arr.length - i];\r\n  }\r\n  return arr;\r\n}',
'function reverseArray(arr) {\r\n  for (let i = 0; i < arr.length / 2; i++) {\r\n    let temp = arr[i];\r\n    arr[i] = arr[arr.length - 1 - i];\r\n    arr[arr.length - 1 - i] = temp;\r\n  }\r\n  return arr;\r\n}',
'Think about swapping elements from both ends. Also, how many iterations do you really need?'),

('medium', 'Fix the bug in this function that should check if a string is a palindrome.',
'function isPalindrome(str) {\r\n  str = str.toLowerCase();\r\n  for (let i = 0; i < str.length; i++) {\r\n    if (str[i] !== str[str.length - i]) {\r\n      return false;\r\n    }\r\n  }\r\n  return true;\r\n}',
'function isPalindrome(str) {\r\n  str = str.toLowerCase();\r\n  for (let i = 0; i < str.length / 2; i++) {\r\n    if (str[i] !== str[str.length - 1 - i]) {\r\n      return false;\r\n    }\r\n  }\r\n  return true;\r\n}',
'Check the index you''re comparing against. Also, do you need to check the entire string?'),

('hard', 'Debug this recursive function that should calculate the nth Fibonacci number.',
'function fibonacci(n) {\r\n  if (n <= 1) return n;\r\n  return fibonacci(n - 1) + fibonacci(n - 2);\r\n}',
'function fibonacci(n) {\r\n  if (n < 0) return null;\r\n  if (n <= 1) return n;\r\n  return fibonacci(n - 1) + fibonacci(n - 2);\r\n}',
'What happens when n is negative? Should you handle that case?');

INSERT INTO challenges (difficulty, description, buggy_code, correct_code, hint) VALUES
-- Easy Challenges
('easy', 'Fix the string concatenation error in this JavaScript code.',
'let firstName = "Tony";\nlet lastName = "Stark"\nconsole.log(firstName + " " + lastname);',
'let firstName = "Tony";\nlet lastName = "Stark";\nconsole.log(firstName + " " + lastName);',
'Check the variable names carefully. JavaScript is case-sensitive!'),

('easy', 'This code should calculate the area of a rectangle, but it\'s not working.',
'function calculateArea(width, height) {\n  return width * heigth;\n}',
'function calculateArea(width, height) {\n  return width * height;\n}',
'Look for typos in the variable names.'),

('easy', 'Fix the array push method that\'s not working correctly.',
'const avengers = ["Iron Man", "Thor"];\navengers.Push("Captain America");',
'const avengers = ["Iron Man", "Thor"];\navengers.push("Captain America");',
'JavaScript methods are case-sensitive. Check the method name.'),

-- Medium Challenges
('medium', 'Debug this function that should remove duplicates from an array.',
'function removeDuplicates(arr) {\n  return arr.filter((item, index) => {\n    arr.indexOf(item) === index;\n  });\n}',
'function removeDuplicates(arr) {\n  return arr.filter((item, index) => \n    arr.indexOf(item) === index\n  );\n}',
'The filter callback needs to return a value. Check if you\'re missing a return statement.'),

('medium', 'Fix the object destructuring syntax in this code.',
'const hero = {name: "Thor", weapon: "Mjolnir"};\nconst {name, power} = hero;\nconsole.log(name, power);',
'const hero = {name: "Thor", weapon: "Mjolnir"};\nconst {name, weapon} = hero;\nconsole.log(name, weapon);',
'Make sure you\'re destructuring properties that actually exist in the object.'),

('medium', 'This Promise chain is not handling errors correctly.',
'fetch("api/avengers")\n  .then(response => response.json())\n  .then(data => console.log(data))',
'fetch("api/avengers")\n  .then(response => response.json())\n  .then(data => console.log(data))\n  .catch(error => console.error(error))',
'What happens if the fetch fails? How do we handle errors in Promises?'),

-- Hard Challenges
('hard', 'Fix this async/await function that\'s causing a memory leak.',
'async function fetchAllAvengers() {\n  const avengers = [];\n  while(true) {\n    const response = await fetch("/api/avengers");\n    const data = await response.json();\n    avengers.push(...data);\n  }\n  return avengers;\n}',
'async function fetchAllAvengers() {\n  const response = await fetch("/api/avengers");\n  const data = await response.json();\n  return data;\n}',
'Think about the while loop. When does it stop? Do we really need it?'),

('hard', 'Debug this recursive function that\'s causing a stack overflow.',
'function findPowerLevel(hero) {\n  if(hero.powerLevel) {\n    return findPowerLevel(hero);\n  }\n  return hero.powerLevel || 0;\n}',
'function findPowerLevel(hero) {\n  if(hero.powerLevel) {\n    return hero.powerLevel;\n  }\n  return 0;\n}',
'Look at the recursive call. When does the recursion stop?'),

('hard', 'Fix the race condition in this async code.',
'let power = 0;\nasync function increasePower() {\n  const currentPower = power;\n  await someAsyncOperation();\n  power = currentPower + 1;\n}',
'let power = 0;\nasync function increasePower() {\n  power = await someAsyncOperation();\n  power += 1;\n}',
'Think about what happens when multiple calls to increasePower happen simultaneously.'),

('hard', 'Debug this closure that\'s causing a memory leak.',
'function createPowerMonitor() {\n  const powers = [];\n  setInterval(() => {\n    powers.push(getCurrentPower());\n  }, 1000);\n  return {\n    getPowers: () => powers\n  };\n}',
'function createPowerMonitor() {\n  const powers = [];\n  const interval = setInterval(() => {\n    powers.push(getCurrentPower());\n    if(powers.length > 10) clearInterval(interval);\n  }, 1000);\n  return {\n    getPowers: () => powers\n  };\n}',
'The interval keeps running forever. How can we limit the data collection?');
