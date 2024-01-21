export interface Email {
    id: number
    sender_email: string
    sender_naam: string
    receiver_email: string
    receiver_naam: string
    titel: string
    queue_date: string
    send_date: string
    body: string
}