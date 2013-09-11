# The Story

I fully expect most people to never open this file. It is pretty much too long to read…But if you're bored at work or truly want to know, I left it here. It is the very first file I created and did so to ensure I thought through my intents and was sure I wanted to continue.

-------

So I'm doing something I said I had no interest in doing. Something I saw no point in doing.

Who am I? Just a guy who has been building web sites for the better part of his life. Since the 90's when we had blinking text and flames on text and animated GIFs. No, not the cool animated GIFs you're thinking of…But we thought they were cool. We had the Apple II turtle and then we got on the web with Geo Cities and Angel Fire and Alpha World and even bulletin boards. Yes, I've dialed in to online message boards before! Amazing, I know. We used to walk up hill both ways for our internet.

The first CMS I ever used was WordPress. Followed by Joomla! followed by Durpal. No, that's a lie. I think I used PHP Nuke first or maybe XOOPS. WordPress was definitely not the first - it was just the one I used for a long while later on and learned from (Joomla! was then my favorite CMS afterward). Anyway, I've always maintained there was no reason to build yet another CMS.

I've changed my mind.

### My Story and Experience With CMS'
Let me give you a little history. We've had CMS' like WordPress and Drupal for ages now and I used them at work years ago while building sites both large and small (including my own blog). In fact, open-source was the entire reason I went from print design to the web. I am a firm believer and supporter of open-source projects.

As I quickly learned in my professional life, using things like WordPress, Joomla!, and Drupal came with an enormous headache. I was hired at a company that was using WordPress for a large site at first followed by Drupal. It was an utter nightmare. It was, in fact, the world's largest Durpal site (and could still be the record holder). The company I worked for even brought in (like literally flew them in and put them up in a hotel) the experts (I'm not naming names here on purpose) and they kinda threw their hands up in the air and said, "We don't know." The main problem was sessions. The codebase and the database (MySQL - and this was before all the lovely cloud solutions) just couldn't handle it. We had a crashing site - constantly.

I braced the site with custom code using, at the time, a new open-source framework called CakePHP. It may have been hitting version 1.0 by the time I used it, but I can't remember. It helped a lot. We were able to keep up with client demand in terms of features (which changed constantly) as well as traffic thanks to a solid codebase with proper caching strategies. Parts of the site became more maintainable.

At this poinst I still said that I'd never build a custom CMS to compete with the likes of WordPress or Drupal. Even though I knew they were failing a certain audience. An audience that could pay even. I still said no.

The reason was simply because I felt that the world didn't need something better. The tools were more than adequate. I still personally used them for my own site (though I quickly shifted to a custom CMS on CakePHP which later became Croogo which later became custom Lithium, but I'm getting ahead of my story).

Then one day the client had the site re-built (another agency) in Symfony. Ok, I would've chosen CakePHP at the time, but whatever…A custom solution that addressed the pain points was great. We now have a really nice example of what happens in the corporate/enterprise world. We try something cheap, it doesn't work we try again, then we brace the buckling foundation until we give in and do something the right way.

Same story for BravoTV.com, though they went straight to Drupal and still may be on it for all I know. Another disaster of a project. Though this was fueled by some brother's sister's cousin's uncle's roommate that had ties to a (again unnamed) company that offered Drupal as an "enterprise" solution. Oh, they couldn't be any farther off from the mark…But hey, client asks and order gets filled…So is agency life.

This is about the time I started seeing companies pop up that tried to turn a profit from open-source code and introduce it into the corporate space. I applaud them for their forward thinking (NBC probably ***still*** uses IE6, but who knows) but unfortunately they chose a technology that was never designed for scale. Not only did they choose an old technology, but they also chose to use something that wasn't designed to ever do what they wanted it to do. Talk about hammering a square peg into a rond hole.

Again, I want to be very clear here. I'm not attacking the existing software out there. If it wasn't for great projects like WordPress and Drupal and Joomla! then I'd likely have never gotten into the web space. I would have stuck with print and editorial design. I owe these projects a lot. I owe the open-source community my career.

### No Good Enteprise Solutions
Let's be clear here. There is no such thing as enterprise WordPress or Drupal. End of story. You can give me all the flak you want, but until you've built a site that **had to and did** support hundreds of millions of visitors per month…Until you've build your own systems and worked in this industry for over 12 years…You're going to take that statement and consider, very carefully, why I'm being so bold as to say it and stand by it.

There are several core reasons.

#### Security
First, security. The enterprise loves security and sadly the number one type of site to get hacked are those that use software like WordPress and Drupal. No objections on this one or I will dump on you my server access logs that constantly have 404 requests for "wp-admin" and what not all over the place. The fact that script kiddies start here is bad. This doesn't bode well for enterprise usage. From untrustworthy 3rd party plugins to core security issues to SQL injection…These systems are riddled with issues and you're in for a maintaince game.

#### Scale
Second, scale. Ok, in the past 8 years we have had some awesome developments with MySQL. It's more scalable in the cloud, etc. Gone are the days where you had to tweak about a million little settings. Granted, nothing replaces good schema design (NoSQL doesn't mean no excuses for lack of schema design)…But any good SQL lover should loathe Drupal for how it changes schema in weird ways shifting data around and leaving anomolies in your database when you go and share fields between various content types.

Bottom line, these systems don't really scale well. Again, this is from first hand experience and this is mostly due to database bottlenecks 9 times out of 10. MySQL is just the wrong choice for these systems. You ***can*** put sessions elsewhere, but it's not how the software was designed. So again you're in for a maintenance game and if you fork things…Well, now you're really screwed.

You really shouldn't use MySQL for a sytem that is so read heavy. However, please understand why MySQL was chosen years ago. That's what the industry standard was, there was very few other options, and most importantly of all - that's what shared hosting had for us to use. It was popular and **cheap**. It was a cost effective solution and it was the right choice years ago. Seriously though, think about Drupal. Why on earth would you use MySQL over something like MongoDB given the choice? You literally have a super flexible application where people can add all sorts of fields and settings at will that change with each version. I hope you weren't a victim of a bug in some migration or upgrade script and had your table dropped…It's been known to happen. So keeping up with all the schema changes is a nightmare. Wouldn't it be nice if we could just shove all the data into a balck hole? Ok database architects, scream your head off at that comment. It's true though. The design in this case dictates the need for a database that simply wasn't a commodity before. Yes, I know some people are making efforts now to use MongoDB with Drupal and WordPress…But they are too entrenched in MySQL at this point.

#### Flexibility
Last core reason is flexibility. Often times large companies (and even smaller users) have very custom needs. If you can't find a 3rd party plugin for your favorite CMS that fits those needs, then you need to build one yourself. What if the system doesn't really provide for a good way to add your plugin? What if you are tempted to alter the core? Well, my friend, you are now in a world of hurt. Altering the core to scale, fix security issues (though those should be pull/patch requests), or add new functionality is the worst thing you can do. It leaves you open for a huge maintenance problem. You'll be a slave to your mess for years (fact) or until you quit.

### The Current Solution (that actually works)
So what do we do? Well, we build custom solutions. That's ultimately what happened in the case of the larger clients sites at the agency I used to work at. It's what happened at many agencies and companies around the world after trying other solutions. The open-source CMS simply didn't cut it. Not that they weren't any good - they just weren't the right fit.

Non-technical corporate people cast off open-source and hate it. Woah! Hang on. **NO!** That is not the right attitude. Open-source not only saved you a bundle (even if it created headaches) it also is the reason why the web is so awesome. We wouldn't be nearly anywhere without open-source initiatives.

Ok, so how can we use open-source for the enterprise? Enter frameworks. Frameworks are designed to do whatever you want. It's not on them to scale. That's on you, the developer, software, and database architect. Frameworks (typically) aren't putting you in a corner.

So we typically build custom applications and CMS' for large companies because they have the budget and that's what they really need. Their needs simply aren't met by WordPress or Drupal and that's ok. Those CMS' are still a very viable solution for hundreds of thousands of users.

Which framework? Well, that depends on the language you're using. For PHP we have CakePHP, FuelPHP, Lithium, Solar, Symfony, Yii, Zend, and more. For Node.js we have Express, Sails, and some others. Java? How about Grails? Speaking of, how about Ruby on Rails? Python? We've got Django. All of these are frameworks that ***can*** solve your problems. I'm not saying they will out of the box. I'm just saying that they are capable if wielded properly.

So the custom solutions works, but it's getting away from the point here. So I'm not counting it as an actual solution because it's stepping around the problem.

#### Enterprise CMS'
What about on the proprietary side? Sure. We do have some "enterprise" systems out there starting with Vinette (or now OpenText) which is perhaps one of the largest. Basically all magazines used this…Until many of them tried using something like WordPress. Microsoft has some solutions (always a friend of corporate) as does Oracle and IBM. Even Adobe now has a "solution" as well.

The problem is these are all expensive and don't benefit from the power of open-source. That is a million eyeballs fixing bugs (please note that nothing is bug free) and people contributing add-ons. They're typically ugly looking too. Far more so than their open-source counterparts.

Are they more secure? Do they scale? Not necessarily. The only security benefit they might have is that hackers aren't interested in them because they aren't a big enough target. 

Wait, isn't there some open-source enterprise solutions? Yea, you have a few open-source CMS' that are really branded for the enterprise like Typo3 and Alfesco. They are a bit more limited (less 3rd party development, etc.) than WordPress and Drupal though. They aren't great solutions either.

All things considered these are pretty common in the corporate world. They are current solutions, even if not ideal. However, this is why you see many companies looking into things like WordPress and Drupal.

This spawned the idea to capitalize on this uncertainty and crazy insane upside down market.

We see companies that brand themselves as "enterprise" Drupal or WordPress providers. Again, it's hogwash. They aren't offering anything that you couldn't get otherwise. They prey on people who don't investigate farther or have the up-front budget for something better. They drain money from customers slowly over time. It is not a great long-term solution trust me. Further, go ahead and ask where your data is going, how secure it is, and when you can have it back or export it. See how far you get. I bet you get a different answer from each company.

### Final Thoughts on the Landscape
Note how large this market is. There are a **ton** of systems out there both free and proprietary. Why on Earth would anyone want to enter the crowded space? 

I don't know, but I see people do it. Or at least try. It gives me a sort of inspiration. It's what got me interested in the web years ago.

Yes, WordPress, Durpal, Joomla!, and other options are just fine for hundreds of thousands of people. I don't want to take away from the hard work there.

Why don't I just work on those projects? Because they are over crowded and have a very complicated committee.

Simply put, there are unmet needs out there. Even if it's a small percentage, it's still quite large. Large enough that people are going through a lot of trouble to try and make the current, inappropriate, solutions work.

I do see a shift in the landscape coming as well. The more we use the internet and the more startups we see that benefit by leveraging open-source projects…The more we will have the enteprise looking to beneit in the same way and become, perhaps, more agile.

### My Solution
So we want security. We want the ability to keep our data secure on our own servers. We want scalability. We want flexibility and usability. We also want it to look good too interms of UX and front-end design. Essentially we want a Ferrari. Ok, on the web, guess what? I can indeed build a Ferrari. Wait, what? We want it for free? Shit. Ok…

The other solutions may come close…They may give you a Ferrari at the price of a Mercedes or BMW…But they aren't quite there yet.

This is why I decided to build yet another CMS. This is why I changed my position on the issue.

I do feel that there is a serious need and I also feel like it's a good challenge.

My solution is to provide a solution that draws upon the power of open-source frameworks and good design capable of scaling with the power of ease of use. I'm aiming to build something as friendly and easy to use as WordPress (if not easier), yet as strong as a custom solution. With the flexibility and, hopefully, community around it building 3rd party add-ons.

It will also be built for today's web. Social media integration and mobile friendly.

I also want it to be pretty. I went to the best design school money could buy (for which I'm still sorely, **sorely,** paying off loans for) and I want to put that to use darnit. I often get too wrapped up in the back-end code to play around with the actual design of a site. Even my own blog is stark and minimalistic. I hate it. I loathe it. I want to do better and I know I can. First order of business after getting this CMS along far enough will be to redo my own site.

I'm going to build this CMS by leveraging the Lithium Framework for PHP because I believe PHP to be good enough for a project like this. It is a language built out of the web and so it belongs to the web. There are several other languages built for the web of course (and many not built for the web but used for it anyway), but PHP is arguably the most popular web language. It's fast to use and easy to learn. It also scales if you know what you're doing. I firmly believe the open-source CMS' we see today that utilize PHP do better than those that don't use PHP. Their communities and usage tend to be larger for a reason. So PHP haters, you're going to have to convert or sit out on this one.

I did consider building this in Node.js though. It was a very close runner up. The reason why I chose PHP was my comfort level and ties to the Lithium Framework. I will be using the Lithium Framework because of its advanced features and rapid application development properties. This means anyone using this CMS will, very easily, be able to bring in other libraries and code.

However, building this CMS will be extremely time consuming. I can't make any big promises either. I can't commit to a regular schedule (hey unless someone decides to fund this or donate a bunch of money or something). However, I can promise that there will be something. Why? Because I plan to use it for myself as well.

It has to work both ways. This is a mutually beneficial project. I'm essentially giving the world the tricks up my sleeve. This is (or will be) pretty much the foundation of all work that I do. In this way I can not only maintain it better, but also actually test it live and in production.

I'm going to borrow pieces of code from other projects I've started and start fresh here. I'm also going to allow other people to contribute and have some influence. I'm going to ***try*** to use popular conventions and tools - even if I don't always agree with them or like them.

I want to be careful of a large committee, but I do want to listen to feedback and for all that is holy…I would love some help.

Interested? Read enough yet? Then let's get started.




