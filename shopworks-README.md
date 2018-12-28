# Shopworks Recruitment Test

Thank you for taking the time to do our technical test. It consists of two parts:

- [A coding test](#coding-test)
- [A few technical questions](#technical-questions)

To submit your solution and answers, you can

1. upload a repository to GitHub, GitLab or whatever code hosting platform you prefer
2. upload a `.zip` containing your git repository to your preferred file sharing platform and send us a link to it 

**Please ensure that it also contains a single markdown file with answers to the technical questions.**

## Coding Test

Our product provides tools for managers to plan staff schedules (rotas), one week at a time. 

Let's call our shop __FunHouse__. 

Staff who are employed to work at __FunHouse__ are __Black Widow__, __Thor__, __Wolverine__, __Gamora__.

Manager: __Stan Lee__.

### Overall project scope: single manning calculation for FunHouse

>>>
As a shop manager

I want to know how many single manning minutes there were in my shop each day of this week

So that I can calculate how much bonus I'll pay out at the end of the week. 
>>>

#### Why this scope is important?

Staff get paid an enhanced _bonus_ supplement when they are working alone in the shop. Shop managers can use the information gathered above to strategically plan new rotas in the future with less single manning hours, reducing the cost of running that shop.


#### Scenario One

>>>
```
Black Widow: |----------------------|
```

__Given__ Black Widow working at FunHouse on Monday in one long shift

__When__ no-one else works during the day

__Then__ Black Widow receives single manning supplement for the whole duration of her shift. 
>>>

#### Scenario Two

>>>
```
Black Widow: |----------|
Thor:                   |-------------|
```

__Given__ Black Widow and Thor working at FunHouse on Tuesday

__When__ they only meet at the door to say hi and bye

__Then__ Black Widow receives single manning supplement for the whole duration of her shift

__And__ Thor also receives single manning supplement for the whole duration of his shift.
>>>

#### Scenario Three

>>>
```
Wolverine: |------------|
Gamora:       |-----------------|
```

__Given__ Wolverine and Gamora working at FunHouse on Wednesday

__When__ Wolverine works in the morning shift

__And__ Gamora works the whole day, starting slightly later than Wolverine

__Then__ Wolverine receives single manning supplement until Gamorra starts her shift

__And__ Gamorra receives single manning supplement starting when Wolverine has finished his shift, until the end of the day.
>>>

#### Scenario Four

>>>
```
Wolverine: |----|    |-----------------|
Gamora:    |----------------|    |-----|
```

__Given__ Wolverine and Gamora working at FunHouse on Thursday

__When__ Both of them work throughout the whole day

__And__ The both have a lunch break each

__Then__ Wolverine receives single manning supplement while Gamorra is on break

__And__ Gamorra receives single manning supplement during Wolverines break.
>>>

### Task requirements

Your task is to implement a class that receives a `Rota` and returns `SingleManning`, a DTO containing the __number of minutes worked alone in the shop each day of the week__.

You'll find a `migration.php` file attached, which is a standard Laravel migration file describing the data structure you will need to implement this code test. 

Design your solution with good practices based on your experience: reusable code, separation of concerns, unit tests, etc. Please ensure your code is easily readable.

There is no time limit to complete the task, but make sure that the following criteria is met:

1. Make sure that all the above scenarios are proven to work.
2. We would like for you to describe another scenario, involving at least three people and implement it too.
3. If you think of one more scenario and/or implement it, that would be a plus.
4. You must include tests.
5. Include a `readme.md` file with instructions how to test your solution.
6. Please only include the files absolutely necessary to complete the task and run the tests. 

## Technical Questions

Please answer the following questions in a markdown file called `Answers to technical questions.md`.

1. How long did you spend on the coding test? What would you add to your solution if you had more time?
2. Why did you choose PHP as your main programming language?
3. What is your favourite thing about Laravel? 
4. What is your least favourite?
5. How would you track down a performance issue in production? Have you ever had to do this?
