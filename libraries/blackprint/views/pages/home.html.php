<div class="row">
	<div class="col-md-9">
		<h1>Blackprint CMS</h1>
		<p>
			Thanks for checking out Blackprint. There's a really, really, long way to go. If you're interested in helping, then send me an e-mail: tom (at) shift8creative.com. Thanks!
		</p>
		<h3>Goals</h3>
		<p>
			The main goal here is to provide the world with an industrial strength, enterprise ready, CMS. For free! The CMS landscape is pretty full, that's true...But did you know they're all using 5-10 year old technology and practices?!
		</p>
		<p>
			The simple fact here is that you need to start fresh. You can't simply upgrade the existing options out there because they are far too entrenched in what was standard years ago. Breaking changes, bugs, security issues, and more. It's just too late for these other solutions. That's not to say they aren't useful in their own way. Just understand their limitations.
		</p>
		<p>
			I don't want to discount hard work, especially from the wonderful open-source communities out there for the various existing CMS options. However, you should not be using MD5 hashed passwords in the year 2013. If you're using PHP, then you really sould be using namespaces and PHP 5.3+ by now and SQL injection shouldn't be plaguing things. In fact, Blackprint uses MongoDB simply to avoid all possibilities of SQL injection by 3rd party add-ons. The security system in Blackprint, thanks to the Lithium Framework, is probably the most secure you'll find in any CMS. There's even plans for two factor authentication right out of the box!
		</p>
		<p>
			It's fast too! Given that we're using PHP 5.3+, we're already seeing a speed increase due to enhancements in the language. However, PHP wasn't really the bottleneck for scaling with many PHP based CMS solutions in the past. In fact, language was rarely the problem for a CMS - no matter the language. It was the database. Again, MongoDB to the rescue! Not only do our sites work with sessions, but they also are read heavy. Since there are less inserts going on here than reads and we aren't taking advantage of a transactional database...Why jam a square peg in a round hole?
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