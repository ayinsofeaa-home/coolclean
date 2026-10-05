import '../login_page.dart';
import 'package:flutter/material.dart';
import 'job_page.dart';
import 'alerts_page.dart';
import 'profile_page.dart';

class Homepage extends StatefulWidget {
  const Homepage({super.key});

  @override
  State<Homepage> createState() => _HomepageState();
}

class _HomepageState extends State<Homepage> {
  bool isOnline = false;
  bool hasActiveJob = true; //kalau true ada order, kalau false takde order
  int currentTab = 0; //untuk track tab yang aktif

  @override
  Widget build(BuildContext context) {
    return Scaffold(

      //APPBAR

      appBar: AppBar(
        backgroundColor: Colors.white,
        title: Text('Driver Home',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          GestureDetector(
            onTap: () {
    Navigator.pushReplacement(
    context,
    MaterialPageRoute(builder: (context) => const LoginPage()),
    );
    },
          child: Container(
            margin: EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Color(0xffF7F8F8),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Image.asset(
              'Assets/logo/logout.png',
              width: 20,
              height: 20,
              color: Colors.lightBlue[900],
            ),
          ),
          )
        ],
      ),
      body: SingleChildScrollView(

        //WELCOME DRIVER

      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Align(
            alignment: Alignment.topCenter,
            child: Padding(
              padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 20),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Image.asset(
                    'Assets/logo/CoolCleanLogo.png',
                    width: 80,
                    height: 80,
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: [
                        Text(
                          'Welcome Driver!',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.lightBlue[900],
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Set your availability and manage pickup jobs.',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey[700],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ), //  tutup Align
          const SizedBox(height: 20),
          Container(
            margin:const EdgeInsets.symmetric(horizontal: 16.0),
            padding: const EdgeInsets.all(16.0),
            decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(13),
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey,
                    blurRadius: 8,
                    offset: Offset(0, 2),
                  )
                ]

            ),

              //WALLET DRIVER

            child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.teal.shade50,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Image.asset('Assets/logo/wallet.png',
                      color: Colors.teal,
                      width: 25,
                    height: 25,
                    ),
                  ) ,
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Driver Wallet',
                          style: TextStyle(color:Colors.lightBlue[900] , fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        const SizedBox(height: 2),
                        Text('Earned from completed jobs',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                        const SizedBox(height: 8),
                        Text('Pending payout: RM 0.00',
                          style: TextStyle(fontSize: 12, color: Colors.orange[800], fontWeight: FontWeight.w600),
                        ),
                        Text('Paid out: RM 0.00',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                  ),
                  Text('RM 0.00',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.teal),
                  ),


                ],
              ),
          ),

          //DRIVER STATUS

          const SizedBox(height: 20),
          Container(
            margin:const EdgeInsets.symmetric(horizontal: 16.0),
            padding: const EdgeInsets.all(16.0),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(13),
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.grey,
                  blurRadius: 8,
                  offset: Offset(0, 2),
                )
              ]
            ),
            child: Row(
              children: [
                Container(
                  padding: EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.teal.shade50,
                    borderRadius: BorderRadius.circular(10),
                  ),
            child: Image.asset(
                isOnline ? 'Assets/logo/online.png' : 'Assets/logo/offline.png',
              color: isOnline ? Colors.green : Colors.red,
              width: 25,
              height: 25,
            )
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isOnline ? 'ONLINE' : 'OFFLINE',
                        style: TextStyle(fontWeight: FontWeight.bold,
                            fontSize: 15,
                          color: isOnline ? Colors.green : Colors.red,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        isOnline
                        ? 'Availability will sync with the server later.' :
                        'You are currently offline and won\'t receive new jobs.',
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
                Switch(value: isOnline,
                    activeThumbColor: Colors.teal,
                    onChanged: (value){
                  setState((){
                    isOnline = value ;
                  }

                  );
                    }),
              ],
            ),
          ),
          const SizedBox(height: 20),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Text(
                'Available Request',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.lightBlue[900],
                )),
          ),

          //AVAILABLE REQUEST

          const SizedBox(height: 10),
          Container(
              margin: const EdgeInsets.symmetric(horizontal: 20.0),
              padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 33),
              decoration: BoxDecoration(
                  color: Colors.white ,
                  borderRadius: BorderRadius.circular(12) ,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.grey.withOpacity(0.15),
                      blurRadius: 8,
                      offset: Offset(0, 2),
                    ),
                  ]
              ),
              child: Column(
                  children: [
                    Image.asset(
                    'Assets/logo/lorry.png',
                      color: Colors.grey ,
                      height: 20,
                      width: 20,
                    ),
                    const SizedBox(height: 8,),
                    Text(
                      'No paid pickup requests are waiting right now.',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[600],
                      ),
                      textAlign: TextAlign.center,
                    )
                  ]
              )
          ) ,

          //MY ACTIVE PROGRESS

          const SizedBox(height: 20),
          Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Text(
              'My Active Progress' ,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
          ),
          const SizedBox(height: 10),
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 20.0),
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 50),
            decoration: BoxDecoration(
                color: Colors.white ,
                borderRadius: BorderRadius.circular(12) ,
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.15),
                    blurRadius: 8,
                    offset: Offset(0, 2),
                  ),
                ]
            ),
            child: hasActiveJob
              ? Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [ //kalau ada order
                Text('Progress: ',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Colors.lightBlue[900],
                  ),
                ),
                const SizedBox(height: 10),
                //Badge Status
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.teal[50],
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    'Completed',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Colors.teal[800],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Laundry service',
                  style: TextStyle(fontWeight: FontWeight.bold,
                      fontSize: 15),
                ),
                const SizedBox(height: 4),
                Text(
                    'Wangsa Maju, Kuala Lumpur, Malaysia',
                    style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                ),
                const SizedBox(height: 16),
                //BUTTON OPEN MAP
                SizedBox(
                  width: double.infinity,
                    child: OutlinedButton.icon(
                        onPressed: () {
                          // TODO: buka Google Maps ikut alamat job ni
                        },
                        icon: Image.asset(
                        'Assets/logo/map.png',
                        color: Colors.grey ,
                        height: 20,
                        width: 20,
                        ),
                      label: Text(
                      'Open Map' ,
                      style: TextStyle(
                          color: Colors.black87,
                          fontWeight: FontWeight.w600
                      ),

                      ),
                      style: OutlinedButton.styleFrom(
                        padding: EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadiusGeometry.circular(12),
                        ),
                        side: BorderSide(color: Colors.grey.shade300),
                      ),
                ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                      onPressed: (){},
                    icon: Icon(Icons.chat_bubble_outline,
                    size: 18,
                        color: Colors.black87
                    ),
                    label: Text(
                      'Timeline/Messages',
                      style: TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.w600 ,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      padding: EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadiusGeometry.circular(12),
                      ),
                      side: BorderSide(color: Colors.grey.shade300),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Container(
                  width: double.infinity,
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.teal[50],
                    borderRadius:BorderRadius.circular(10),
                  ),
                  child: Text(
                    'This Job is Completed',
                    style: TextStyle(
                    fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Colors.teal[800],
                    ),
                  ),

                )
                // ... content lain kalau "ada job" ...
              ],
            )
                : Column(
              children: [ //kalau takde order
                Image.asset(
                  'Assets/logo/noOrder.png',
                  color: Colors.grey ,
                  height: 20,
                  width: 20,
                ),
                Text(
                  'You don\'t have any active job right now.',
                  style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),





          // 🆕 Container baru boleh letak sini kalau nak

        ], //  TAMBAH — tutup children punya Column
      ), //  TAMBAH — tutup Column
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: currentTab,
        selectedItemColor: Colors.lightBlue[900],
        unselectedItemColor: Colors.grey,
        onTap: (index)  async{
          if (index == 1) {
            // Tab "Jobs" — navigate ke JobPage
            await Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const JobPage()),
            );
            // Lepas balik dari JobPage, reset semula ke tab Home
            setState(() {
              currentTab = 0;
            });
          }

          else if (index == 2) {
            // Tab "Alerts"
            await Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const AlertPage()),
            );
            setState(() {
              currentTab = 0;
            });
          }
          else if (index == 3) {
            // Tab "Profile"
            await Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const ProfilePage()),
            );
            setState(() {
              currentTab = 0;
            });
          }

          else {
            setState(() {
              currentTab = index;
            });
          }
        },
        items:[
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/homepage.png' ,
              width: 24,
              height: 24,
              color: currentTab == 0 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/job.png' ,
              width: 24,
              height: 24,
              color: currentTab == 1 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Jobs',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/alerts.png' ,
              width: 24,
              height: 24,
              color: currentTab == 2 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Alerts',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
                'Assets/logo/profile.png' ,
              width: 24,
              height: 24,
              color: currentTab == 3 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Profile',
          ),
        ],
      )
    );
  }
}