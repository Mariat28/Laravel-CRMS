<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Group;
use App\Models\Issue;
use App\Models\User;
use App\Models\TicketPriority;
use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function addticket(Request $req)
    {
        Issue::create([
            'body' => $req->description,
            'subject' => $req->subject,
            'source' => $req->source,
            'created_by' => Auth::user()->name,
            'priority_id' => $req->priority,
            'duration' => 0,
            'status_id' => null,
            'user_id' => null,
        ]);
            // session()->put('success','new ticket created');
        return redirect('/tickets')->with("ticket_created","A new ticket has been created");
       
    }
    /**
     * @return \Illuminate\Http\Response
     */

    public function ticket()
    {
        //Fetch all tickets specific to this company
        $tickets = Issue::where('company_id', Auth::user()->company_id)->get();

        //Fetch unassigned tickets specific to this company
        $neededTickets = Issue::where('company_id', Auth::user()->company_id)->where('status_id', 5)->get();

        //Fetch tickets specific to this company
        $priorityList=TicketPriority::all();

        $closedTickets = 0;
        $pendingTickets = 0;
        $openTickets = 0;
        $dueTickets = 0;
        $unassignedTickets = 0;

        foreach($tickets as $ticket){
            if($ticket->status_id == 1){
                $closedTickets += 1;
            }
            elseif($ticket->status_id == 2){
                $pendingTickets += 1;
            }
            elseif($ticket->status_id == 3){
                $openTickets += 1;
            }
            elseif($ticket->status_id == 4){
                $dueTickets += 1;
            }
            elseif($ticket->status_id == 5){
                $unassignedTickets += 1;
            }
        }

        //Fetch departments specific to this company
        $departments = Group::where('company_id', Auth::user()->company_id)->get();  


        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }

        #echo json_encode($departmentTickets);
        return view('adminandsupervisor.tickets', compact('neededTickets', 'closedTickets','departments','pendingTickets','priorityList','openTickets','departmentTickets', 'unassignedTickets')); 
    }


    public function openticket()
    {
        //Fetch open tickets specific to this company
        $tickets = Issue::where('company_id', Auth::user()->company_id)->where('status_id', 3)->get();
        
        //Fetch departments specific to this company
        $departments = Group::where('company_id', Auth::user()->company_id)->get();

        //Fetch priority list specific to this company
        $priorityList=TicketPriority::all();

        $departmentTickets = [];

        $closedTickets = 0;
        $pendingTickets = 0;
        $openTickets = 0;
        $dueTickets = 0;
        $unassignedTickets = 0;

        foreach($tickets as $ticket){
            if($ticket->status_id == 1){
                $closedTickets += 1;
            }
            elseif($ticket->status_id == 2){
                $pendingTickets += 1;
            }
            elseif($ticket->status_id == 3){
                $openTickets += 1;
            }
            elseif($ticket->status_id == 4){
                $dueTickets += 1;
            }
            elseif($ticket->status_id == 5){
                $unassignedTickets += 1;
            }
        }

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('adminandsupervisor.opentickets', compact('opentickets', 'departmentTickets','tickets','pendingtickets','closedtickets', 'departments','prioritylist'));
      
    }


    public function closedticket()
    {
        $closedtickets = Issue::where('status_id', 1)->get();
        $tickets = Issue::where('status_id', null)->get();
        $departments = Group::where('company_id', Auth::user()->company_id)->get();
        $prioritylist=TicketPriority::all();
        $opentickets = Issue::where('status_id', 3)->get();
        $pendingtickets = Issue::where('status_id', 2)->get();
        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('adminandsupervisor.closedtickets', compact('closedtickets', 'departmentTickets','opentickets','pendingtickets','tickets', 'departments','prioritylist'));

    }


    //pending tickets
    public function pendingticket()
    {
        $pendingtickets = Issue::where('status_id', 2)->get();
        $tickets = Issue::where('status_id', null)->get();
        $departments = Group::where('company_id', Auth::user()->company_id)->get();
        $prioritylist=TicketPriority::all();
        $opentickets = Issue::where('status_id', 3)->get();
        $closedtickets = Issue::where('status_id', 1)->get();
        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('adminandsupervisor.pendingtickets', compact('closedtickets', 'departmentTickets','opentickets','pendingtickets','tickets', 'departments','prioritylist'));
  }


    //  retrieve one ticket's details
    public function ticketdetails($id, Request $req)
    {
        $data = Issue::find($id);
        $agents = User::where('userrole_id', 3)->get();
        $tickets = Issue::where('status_id', null)->get();
        $departments = Group::where('company_id', Auth::user()->company_id)->get();
        $opentickets = Issue::where('status_id', 3)->get();
        $pendingtickets = Issue::where('status_id', 2)->get();
        $closedtickets = Issue::where('status_id', 1)->get();
        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('adminandsupervisor.ticketdetails', compact('data', 'agents', 'departmentTickets','tickets', 'departments','opentickets','pendingtickets','closedtickets'));
    }


    //retrieve openticketdetails
    public function openticketdetails($id)
    {
        $ticket = Issue::find($id);
        $reply = Reply::where('ticket_id', $id)->first();
        $comments = $ticket->comments;
        $departments = Group::where('company_id', Auth::user()->company_id)->get();
        $tickets = Issue::where('status_id', null)->get();
        $opentickets = Issue::where('status_id', 3)->get();
        $pendingtickets = Issue::where('status_id', 2)->get();
        $closedtickets = Issue::where('status_id', 1)->get();
        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('openticketdetails', compact('ticket', 'reply', 'comments', 'tickets', 'departments','opentickets','closedtickets','pendingtickets','departmentTickets'));
    }
    //retrieve closedticket details
    public function closedticketdetails($id){
        $ticket = Issue::find($id);
        $creator=User::where('id',$ticket->user_id)->get();
        $reply =  Reply::where('ticket_id', $id)->get();
        $comments = $ticket->comments;
        $departments = Group::where('company_id', Auth::user()->company_id)->get();
        $tickets = Issue::where('status_id', null)->get();
        $opentickets = Issue::where('status_id', 3)->get();
        $pendingtickets = Issue::where('status_id', 2)->get();
        $closedtickets = Issue::where('status_id', 1)->get();
        $departmentTickets = [];

        foreach($departments as $department){
            if ($department->users)
            {
                $totalTickets = 0;
                foreach($department->users as $user)
                {
                    if($user->tickets)
                    {
                        $totalTickets += count($user->tickets);
                    }
                }

                array_push($departmentTickets, ["id" => $department->id, "name" => $department->name, "tickets"=> $totalTickets]);
            }
        }
        return view('adminandsupervisor.closedticketdetails', compact('ticket', 'reply','creator', 'comments', 'tickets', 'departments','opentickets','closedtickets','pendingtickets','departmentTickets'));
    
    }


    //reply to ticket
    public function ticketreply(Request $req)
    {
        Reply::create([
            'reply' => $req->reply,
            'user_id' => Auth::user()->id,
            'ticket_id' => $req->id,
        ]);

        //update the ticket status to an open ticket
        Issue::where('id', $req->id)->update(['status_id' => 3, 'user_id' => Auth::user()->id]);
        return redirect()->route('tickets');
    }

    // update ticket status
    public function changeTicketstatus(Request $request)
    {
      
        Issue::where('id', $request->id)->update(['status_id' => '1']);
        Reply::create([
            'reply'=>"Closed",
            'user_id'=>Auth::user()->id,
            'ticket_id'=>$request->id,
        ]);
        return redirect()->route('tickets');
    }
    //delete ticket
    public function deleteticket($id){
        Reply::where('ticket_id',$id)->delete();
        Comment::where('ticket_id',$id)->delete();
        Issue::where('id', $id)->delete();
        return redirect()->route('closedtickets');

    }
    public function assign(Request $request)
    {
        $id = intval($request->agent);
        Issue::where('id', $request->ticketid)->update(['user_id' => $id]);
        //update the ticket status to a pending ticket
        Issue::where('id', $request->ticketid)->update(['status_id' => 2, 'user_id' => Auth::user()->id]);
        return redirect()->route('tickets');
    }


    //comment on ticket
    public function addcomment(Request $req)
    {
        Comment::create([
            'comment' => $req->comment,
            'ticket_id' => $req->id,
            'user_id' => Auth::user()->id,
        ]);

        return back();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //return view()
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticketid)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
