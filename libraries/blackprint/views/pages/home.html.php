<div class="row">
	<div class="col-md-9">
		<h1>Blackprint CMS</h1>
		<p>
			Thanks for checking out Blackprint. There's a really, really, long way to go. If you're interested in helping, then dig in, start submitting issues and or pull requests. Thanks!
		</p>
		<h3>Goals</h3>
		<p>
			The main goal here is to provide the world with an industrial strength, enterprise ready, CMS. For free! The CMS landscape is pretty full, that's true...But there aren't many industrial strength enterprise ready CMS options out there.
		</p>
		<p>
			What I mean by that is web applications that scale to handle many visitors, have good cryptography for their passwords, and can implement advanced forms of security such as two-factor authentication. CMS' that are flexible enough to keep up with custom needs, yet are also easy to maintain. This is very important for the enterprise where you have large teams and very specific needs both in terms of features and security.
		</p>
		<p>
			I don't want to discount hard work, especially from the wonderful open-source communities out there for the various existing CMS options. It is just my belief that the CMS landscape has been itching for a fresh start for a long while now. The existing solutions are great and have worked for years, but we see more and more companies spring up that offer customized versions of those existing systems for the enterprise or just as a way to obtain a clean and secure solution. Every hosted WordPress or Drupal service out there is literal proof that we need something new. I'm not saying for everyone but, for many, we do need something fresh.
		</p>
		<p>
			Blackprint features many of these advanced authentication and security features out of the box. It scales well and is very flexible. It's fast too! Given that we're using PHP 5.3+, we're already seeing a speed increase due to enhancements in the language. However, PHP wasn't really the bottleneck for scaling with many PHP based CMS solutions in the past. In fact, language was rarely the problem for a CMS - no matter the language. It was the database. MongoDB to the rescue! Not only do our sites work with sessions, but they also are read heavy. Since there are less inserts going on here than reads and we aren't taking advantage of a transactional database...Why jam a square peg in a round hole?
		</p>
		<p>
			So by utilizing a powerful framework like Lithium, using PHP 5.3+, and MongoDB, Blackprint is able to provide an extremely fast and scalable system. Best of all, if you did need a SQL database for any specific reason, you can still use it! The Lithium Framework is extremely flexible and Blackprint's core only requires MongoDB. In fact, nothing prevents you from bringing your own code from other projects to use in this system.
		</p>
		<p>
			There are many longer term goals, but the first was a beautiful open-source CMS both in design and software architecture that scaled. More to come soon.
		</p>
	</div>
	<div class="col-md-3"></div>
</div>